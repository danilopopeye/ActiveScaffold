<?php
define('BASEPATH','../');
define('DS',DIRECTORY_SEPARATOR);

// TODO: find a better place to put the include :(
include( BASEPATH . '..'. DS .'helpers'. DS .'inflector_helper.php');

class ActiveScaffold {
    var $actions = array('M','MODEL','C','CONTROLLER', 'L', 'LIST');
    var $conn;
    var $args;
    var $tables;
    var $reserved = array(
        'Controller', 'CI_Base', '_ci_initialize', '_ci_scaffolding', 'index'
    );

    function run( $args ){
        $this->connect();

        array_shift( $args );

        $this->args = $args;
        
        if( isset( $this->args[0] ) ){
            $action = $this->parseAction( $this->args[0] );

            if( $action == 'list' ){
                $table = $this->listTables();
                
                return;
            }

            return $this->getAction( $this->args[0], $this->args[1] );
        }

        $action = $this->parseAction( $this->menu() );
        
    }

    function getAction( $type, $name = FALSE ){
        if( ! $type ){
            // TODO: Redirect to menu
            echo "Invalid action!";
            return FALSE;
        }

        $name = strtolower( $name );

        if( $this->getInput( 'Build a '. $type .' called "'. $name .'"', array('y','n') ) == 'n' ){
            // TODO: Redirect to menu
            return FALSE;
        }

        // TODO: validate the type of the response
        if( $this->save( $name, $type ) === TRUE ){
            echo ucwords( $type ) .' salvo!';
        }
    }

    function save( $name, $type ){
        $dir = BASEPATH . $type . 's';

        if( ! is_dir( $dir ) ){
            return FALSE;
        }

        // TODO: Validate first
        if( is_file( $dir . DS . $name . '.php' ) ){
            echo 'Ja existe um ' . $type . ' chamado ' . $name;
            return FALSE;
        }

        $template = $this->parseTemplate( $name, $type );

        if( $template === FALSE ){
            echo 'Erro ao parsear o template do ' . $type . ' ' . $name;
            return FALSE;
        }

        $isOk = @file_put_contents( $dir . DS . strtolower( $name ) . '.php', $template );

        if( $isOk === FALSE ){
            echo 'Erro ao salvar o arquivo';
            return FALSE;
        }

        return TRUE;
    }

    function getInput( $message, $options = "" ){
        if( is_array( $options ) ){
            $options = '[ ' . implode( ' | ', $options ) .' ]';
        }

        echo ' '. $message .'? '. $options ."\n:";

        return strtolower( trim( fgets( STDIN ) ) );
    }

    function parseAction( $a ){
        $a = strtoupper( $a );

        if( ! in_array( $a, $this->actions ) ){
            return FALSE;
        }

        if( $a == 'M' OR $a == 'MODEL' ){
            return 'model';
        } else if ( $a == 'C' OR $a == 'CONTROLLER' ){
            return 'controller';
        } else if ( $a == 'L' OR $a == 'LIST' ){
            return 'list';
        }
    }

    // TODO: respond a array with name and filename
    function parseTemplate($name, $template){
        $buff = @file_get_contents( $template );

        if( $buff === FALSE ){
            return FALSE;
        }

        $buff = str_replace( '{name}', ucwords( $name ), $buff );
        $buff = str_replace( '{model}', plural( ucwords( $name ) ), $buff );
        $buff = str_replace( '{lowerame}', strtolower( $name ), $buff );

        return $buff;
    }

    function menu(){
        echo " ActiveScaffold Menu\n";
        echo " -------------------------\n";
        echo " [L]ist tables\n";
        echo " [M]odel\n";
        echo " [C]controller\n";
        echo " -------------------------\n\n";

        return $this->getInput( 'Enter a action', array('L','M','C') );
    }

    function connect(){
        include( BASEPATH . 'config'. DS .'database.php' );

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

    function listTables(){
        $q = mysql_query('show tables;', $this->conn);
        
        if( mysql_num_rows( $q ) == 0 ){
            return FALSE;
        }

        while( $t = mysql_fetch_array( $q ) ){
            $this->tables[ ++$i ] = $t[0];
            
            echo '[' . $i . '] ' . $t[0] . "\n";
        }
        
        return $this->getInput('Choose a table',array('number'));
    }
}

?>
