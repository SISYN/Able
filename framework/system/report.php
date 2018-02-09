<?php
global $db, $pdb, $m, $me;

define('REPORTING_CHECK_PREVIOUS', true);
define('REPORTING_PREVIOUS_INTERVAL', 1800);
define('REPORTING_UPDATE_PREVIOUS_TIME', true);

class Report {

    private $db, $pdb, $u, $me;
    private $Reports, $MyMemberID;
    private $ContentTypeTables;

    /******************************************************************************************************************
     * __construct() -
     * @param mixed $data - Accepts a string with colon separator for content-type:content-id:additional-info
     * or allows you pass an array directly to the Reports var
     *****************************************************************************************************************/
    function __construct($data=null) {
        // Begin by making sure all our global variables are defined and working as expected
        global $db, $pdb, $m, $me;

        if ( !isset($db) || !is_object($db) )
            $db = new DB;
        if ( !isset($pdb) || !is_object($pdb) )
            $pdb = new PDB;
        if ( !isset($m) || !is_object($m) )
            $m = new Membership;
        if ( !isset($me) || !is_array($me) )
            $me = ($m->AttemptLogin()) ? $m->Attr() : [];

        $this->db = $db;
        $this->pdb = $pdb;
        $this->m = $m;
        $this->me = $me;

        // List the respective table to search for an id based on a name if name is supplied
        $this->ContentTypeTables = [
            'error'=>'merkd_errors',
            'match'=>'merkd_matches'
        ];

        // When reports are stored in the db, there is a row `member_id` so we go ahead
        // and get their user id squared away regardless if they're logged in/out at this time
        $this->MyMemberID = ( !isset($me['id']) ) ? 0 : $me['id'];

        // Just store a blank array for now so it can be added to quickly later (eg: $this->Reports[] = $var;)
        $this->Reports = [];

        // Handle any data that may be passed

        // If its a string, treat as QuickCommand, else treat as Raw data for Reports array
        if ( is_string($data) )
            $this->QuickCommand($data);
        else if ( is_array($data) )
            $this->Raw($data);
        else
            return $this;

        // Now that we can be sure we are only affecting instances with data passed to the construct,
        // we are free to auto-send this report or anything else we may want to do for quick invocation
        // of the class

        return $this->Send();
    }

    /******************************************************************************************************************
     * Previous() - Returns true if previous record is found, false if not
     * @param mixed $Data - can accept an array of data for Raw() or a string for QuickCommand()
     * @param int $PreviousReportInterval - Set the time in seconds on which you'd like PreviousReports to be based
     * @return bool
     *****************************************************************************************************************/
    public function Previous($Data, $PreviousReportInterval=REPORTING_PREVIOUS_INTERVAL) {
        if ( is_string($Data) )
            $this->QuickCommand($Data);
        else if ( is_array($Data) )
            $this->Raw($Data);
        else
            return false;

        $this->SanitizeReportData();
        // now fetch and clear the latest created entry
        $r = $this->Reports[sizeof($this->Reports)-1];
        unset($this->Reports[sizeof($this->Reports)-1]);

        // Attempt to find previous report
        $condition = ($this->MyMemberID!=0)?'(member_id='.$this->MyMemberID.' OR ip=\''.$r['ip'].'\')' : 'ip=\''.$r['ip'].'\'';
        $previous = $this->db->Fetch('merkd_reporting', 'id', $condition . '
                    AND created>='.(time()-$PreviousReportInterval).' ORDER BY id DESC LIMIT 1');

        if ( !$previous )
            return false;

        return $previous['id'];
    }



    /******************************************************************************************************************
     * QuickCommand() - Processes a complete report from a single-line, colon-separated command
     * @param string $Command - colon separated list of data (content-type:content-id:additional-info)
     * @return Object
     *****************************************************************************************************************/
    public function QuickCommand($Command) {
        $split = preg_split('#:+#', $Command);
        if ( sizeof($split) < 2 )
            return false;

        // Order is content-type, content-id, additional-info
        $this->Reports[] = [
            'content-type'=>$split[0],
            'content-id'=>$split[1],
            'additional-info'=>(isset($split[2]))?$split[2]:''
        ];

        return $this;
    }

    /******************************************************************************************************************
     * Raw() - Accepts an array of data to the pass to the reports cache
     * @param Array $Data - array of data for the report
     * @return Object
     *****************************************************************************************************************/
    public function Raw($Data) {

        if ( !isset($Data['content-type']) || !isset($Data['content-id']) )
            return false;

        $this->Reports[] = $Data;

        return $this;
    }

    /******************************************************************************************************************
     * Send() - Sends generated reports to the database
     * @param bool $CheckForPreviousReport - Set to false if you want to force a new report
     * @param int $PreviousReportInterval - Set the time in seconds on which you'd like PreviousReports to be based
     * @param bool $UpdatePreviousReportTimes - If true and PreviousReport is found, its time will be updated to now
     * @return Object
     *****************************************************************************************************************/
    public function Send($CheckForPreviousReport=REPORTING_CHECK_PREVIOUS, $PreviousReportInterval=REPORTING_PREVIOUS_INTERVAL, $UpdatePreviousReportTimes=REPORTING_UPDATE_PREVIOUS_TIME) {
        // First, loop through all the Reports and change hyphen (-) separated indexes to underscore (_) separated
        $this->SanitizeReportData();

        // Now, loop through all the reports and add them to the database
        foreach($this->Reports as $r) {
            if ( $CheckForPreviousReport ) {
                // Attempt to find previous report
                $previous = $this->Previous($r, $PreviousReportInterval);

                if ( !!$previous && $UpdatePreviousReportTimes )
                    $this->db->Update('merkd_reporting', [
                        'created'=>time()
                    ], 'id='.$previous);

                if ( !!$previous )
                    continue;
            }

            // All previous reports have been checked, go ahead and insert a new report
            $this->db->Create('merkd_reporting', $r);
        }

        return $this;
    }

    /******************************************************************************************************************
     * SanitizeReportData() - Loop through all the Reports and change hyphen (-) separated indexes to underscore (_)
     * separated list for the database, will also accept space ( ) and period (.) separators - Also adds the ip, ref,
     * and member-id attributes to the data array
     * @return Object
     *****************************************************************************************************************/
    private function SanitizeReportData() {
        foreach($this->Reports as $ReportID=>$ReportData) {

            // Make sure all keys are underscore separated for the db
            foreach($ReportData as $key=>$val) {
                unset($this->Reports[$ReportID][$key]);
                $ModifiedKey = str_replace(['-', ' ', '.'], '_', $key);
                $this->Reports[$ReportID][$ModifiedKey] = $val;

                if ( $ModifiedKey == 'content_id' ) {
                    // Make sure its a numerical ID, if not, find its ID
                    if ( !is_int($val) ) {
                        if ( !isset($this->ContentTypeTables[$this->Reports[$ReportID]['content_type']]) ) {
                            // store the failed id in the additional_info and set the id to 0
                            $this->Reports[$ReportID]['additional_info'] = '[failed content-id: `'.$val.'`] '.$this->Reports[$ReportID]['additional_info'];
                        } else {
                            // look up its numerical id
                            $find = $this->db->Fetch($this->ContentTypeTables[$this->Reports[$ReportID]['content_type']], 'id', 'name=\''.$val.'\'');
                            if ( !$find )
                                $this->Reports[$ReportID]['additional_info'] = '[failed content-id with search: `'.$val.'`] '.$this->Reports[$ReportID]['additional_info'];
                            else
                                $this->Reports[$ReportID]['content_id'] = $find['id'];
                        }
                    }
                }

            }


            if (!isset($this->Reports[$ReportID]['content_type']) || !isset($this->Reports[$ReportID]['content_id'])) {
                unset($this->Reports[$ReportID]);
                continue;
            }

            // Set accompanying information
            $this->Reports[$ReportID]['ref'] = (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'undefined';
            $this->Reports[$ReportID]['ip'] = (isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown';
            $this->Reports[$ReportID]['member_id'] = $this->MyMemberID;
            $this->Reports[$ReportID]['created'] = time();
            if ( !isset($this->Reports[$ReportID]['additional_info']) )
                $this->Reports[$ReportID]['additional_info'] = '';
        }

        return $this;
    }

    /******************************************************************************************************************
     * Match() - Generates a report for a particular match with the supplied ID
     * @param int $MatchID - The ID of the match to report (can be textual ID or numerical ID)
     * @return Object
     *****************************************************************************************************************/
    public function Match($MatchID) {


        return $this;
    }

    /******************************************************************************************************************
     * Error() - Generates a report for a particular error with the supplied ID
     * @param int $ErrorCode - The code of the error to report (can be textual code or numerical code)
     * @return Object
     *****************************************************************************************************************/
    public function Error($ErrorCode) {


        return $this;
    }

    /******************************************************************************************************************
     * Member() - Generates a report for a particular member with the supplied ID
     * @param int $MemberID - The id of the member to report (numerical ID))
     * @return Object
     *****************************************************************************************************************/
    public function Member($MemberID) {


        return $this;
    }


    function __destruct() {

    }

}










































?>
