<?php
define('BASEPATH','../');

class ActiveScaffold {
    private $actions = array('M','C','V');
    private $conn;
    private $args;

    public function run( $args ){
        $this->args = $args;

        $this->getAction();
    }

    private function getAction(){
        switch ( count($this->args) ){
            case 2:
                
            break;

            case 1:
            default: 
                $this->menu();
            break;
        }
    }

    private function menu(){
        echo " ActiveScaffold Console\n";
        echo " -------------------------\n";
        echo " [M]odel\n";
        echo " [C]controller\n";
        echo " -------------------------\n";
        echo " What do you wanna do ?\n";
    }

    private function connect(){
        include( BASEPATH . 'config/database.php' );

        if( ! isset($active_group) OR ! isset($db) OR count($db) == 0 ){
            return FALSE;
        }

        $db = $db[ $active_group ];

        $this->conn = mysql_connect(
            $db['hostname'], $db['username'], $db['password'], TRUE
        );

        if( ! $this->conn ){
            return FALSE;
        }

        if( ! @mysql_select_db($db['database'], $this->conn) ){
            return FALSE;
        }

        unset( $db, $active_group );

        return TRUE;
    }
}

?>
