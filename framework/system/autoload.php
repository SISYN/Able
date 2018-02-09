<?php
/******************************************************************************************************************
 * Adom / framework / system / autoload.php
 * The autoloader for all other classes, must be called early/first
 *****************************************************************************************************************/
 namespace System {

   class AutoLoad {
     private $Path;
     function __construct() {}
     public function Import($ClassName) {
       // Generate possible file paths and attempt to find the class file
       $this->Path($ClassName);

       if ( $this->Path ) // Path finding was successful
        require_once $this->Path;

       return $this;
     }

     private function Path($ClassName) {
       // Change namespace \ to / so that namespace/class.php will automatically be found
       $ClassName = str_replace('\\', '/', $ClassName);

        $ThisDir = dirname(__FILE__);
        $KnownClassDirs = [
          'base'               =>     $ThisDir . '/../', // framework root
          'project_central'    =>     $ThisDir . '/../../project/',
          'import_foreign'     =>     $_SERVER['DOCUMENT_ROOT'] . '/import/',
          'import_local'       =>     $_SERVER['DOCUMENT_ROOT'] . '/framework/import/',
          'import_project'     =>     $_SERVER['DOCUMENT_ROOT'] . '/framework/project/'
        ];
        $PossibleClassFileNames = [
          'default'    =>  $ClassName,
          'lowercase'  =>  strtolower($ClassName),
          'uppercase'  =>  strtoupper($ClassName)
        ];
        $PossibleClassFileExtensions = [
          'php'=>'.php'
        ];

        $this->Path = false;
        foreach($KnownClassDirs as $Type=>$FileDir) {
          foreach($PossibleClassFileNames as $Case=>$FileName) {
            foreach($PossibleClassFileExtensions as $Lang=>$FileExt) {
              if ( file_exists( $FileDir . $FileName . $FileExt ) ) {
                $this->Path = $FileDir . $FileName . $FileExt;
                break;
              }
            } // End file extensions loop
            if ( $this->Path )
              break;
          } // End file names loop


          // Try to search for namespace dir
          if ( !$this->Path ) {
            //$this->PathNamespace($ClassName);
          }

          // Try to search for split dir
          if ( !$this->Path ) {
            $this->PathSplit($FileDir, $ClassName, '___');
          }


          if ( $this->Path )
            break;
        } // End file dirs loop

        return $this;

     }

     // Does not work, just returns Adom/Base for the namespace
    public function PathNamespace($ClassName) {
      $NamespacePath = str_replace("\\","/",__NAMESPACE__);
      $NamespacePath = str_replace('Adom/Base', '', $NamespacePath);
      $ClassName = str_replace("\\","/",$ClassName);
      $ClassFile = '../project/';
      if ( strlen($NamespacePath) )
      $ClassFile .= $NamespacePath.'/';

      $ClassFile .= "{$ClassName}.php";
      echo 'Namespace file would be '.__NAMESPACE__;

      $this->Path = $ClassFile;

      return $this;
    }

     private function PathSplit($BaseDir, $ClassName, $Delimiter) {
       $ClassNameParts = explode($Delimiter, $ClassName);
       $LastClassNamePart = $ClassNameParts[  sizeof($ClassNameParts) - 1  ];
       unset(   $ClassNameParts[  sizeof($ClassNameParts) - 1  ]   );

       $this->Path = false;
       $ClassFileLocationBase = join('/', $ClassNameParts) . '/';

       $PossibleSplitDirs = [
         'default'    =>  $ClassFileLocationBase . $LastClassNamePart,
         'lowercase'  =>  strtolower($ClassFileLocationBase . $LastClassNamePart),
         'uppercase'  =>  strtoupper($ClassFileLocationBase . $LastClassNamePart),
       ];
       $PossibleSplitDirFileNames = [
         'no-split'  =>  '',
         'default'   =>  '/main',
         'optional'  =>  '/index',
       ];
       $PossibleSplitDirFileNameExtensions = [
         'php' => '.php'
       ];


       foreach($PossibleSplitDirs as $Type=>$FileDir) {
         foreach($PossibleSplitDirFileNames as $Case=>$FileName) {
           foreach($PossibleSplitDirFileNameExtensions as $Lang=>$FileExt) {
             echo 'Checking path '.$BaseDir . $FileDir . $FileName . $FileExt. " <br />\n";
             if ( file_exists( $BaseDir . $FileDir . $FileName . $FileExt ) ) {
               $this->Path = $BaseDir . $FileDir . $FileName . $FileExt;
               break;
             }
           } // End file extensions loop
           if ( $this->Path )
             break;
         } // End file names loop
         if ( $this->Path )
           break;
       } // End file dirs loop



        return $this;

     }


     function __destruct()  {}
   }

 }

?>
