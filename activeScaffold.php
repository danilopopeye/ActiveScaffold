<?php
define('BASEPATH','../');
define('DS',DIRECTORY_SEPARATOR);
define('BR',"\n");

// TODO: find a better place to put the include :(
include( BASEPATH . '..'. DS .'helpers'. DS .'inflector_helper.php');

class ActiveScaffold {
    var $conn;
    var $args;
    var $tables;
    var $fields;
    var $database;
    var $actions = array(
        'M','MODEL','C','CONTROLLER', 'L', 'LIST', 'B', 'BOTH'
    );
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
                return $this->listAction();
            }

            return $this->getAction( $this->args[0], $this->args[1] );
        }

        $action = $this->parseAction( $this->menu() );

        $this->getAction( $action );
    }

    function getAction( $type = FALSE, $name = FALSE, $index = FALSE ){
        if( ! $type OR ! $name OR ( $type == 'model' && ! $index ) ){
            return FALSE;
        }

        $name = strtolower( $name );
        
        if( $type == 'controller' ){
        	$name = singular( $name );
        }

        if( $this->getInput( 'Build a '. $type .' called "'. ucwords( $name ) .'"', array('y','n') ) == 'n' ){
            // TODO: Redirect to menu
            return FALSE;
        }

        // TODO: validate the type of the response
        if( $this->save( $name, $type, $index ) === TRUE ){
            echo ucwords( $type ) .' salvo!' . BR;
        }
    }

    function save( $name, $type, $index ){
        $dir = BASEPATH . $type . 's';

        if( ! is_dir( $dir ) ){
            return FALSE;
        }

        // TODO: Validate first
        if( is_file( $dir . DS . $name . '.php' ) ){
            echo 'Ja existe um ' . $type . ' chamado ' . $name;
            return FALSE;
        }

        $template = $this->parseTemplate( $name, $type, $index );

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

        echo ' '. $message .'? '. $options . BR . ':';

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
        } else if ( $a == 'B' OR $a == 'BOTH' ){
            return 'both';
        }
    }
    
    function listAction(){
        $table = $this->listTables();
        
        $this->parseTable( $table );
    }

    // TODO: respond a array with name and filename
    function parseTemplate($name, $template, $index){
        $buff = @file_get_contents( $template );

        if( $buff === FALSE ){
            return FALSE;
        }

        $buff = str_replace( '{name}', ucwords( $name ), $buff );
        $buff = str_replace( '{lowername}', strtolower( $name ), $buff );

		switch( $template ){
			case 'model':
				$buff = str_replace( '{fields}', $this->parseFields( $index ), $buff );
				break;
			case 'controller':
				// TODO: Dont gess the model name
		        $buff = str_replace( '{model}', plural( ucwords( $name ) ), $buff );
		        break;
		}

        return $buff;
    }

    function menu(){
        echo " ActiveScaffold Menu" . BR;
        echo " -------------------------" . BR;
        echo " [L]ist tables" . BR;
        echo " [M]odel" . BR;
        echo " [C]controller" . BR;
        echo " -------------------------" . BR . BR;

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

        $this->database = $db['database'];

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

        echo 'Tables form: ' . $this->database . BR;

        while( $t = mysql_fetch_row( $q ) ){
            $this->tables[ ++$i ] = $t[0];

            echo ' [' . $i . '] ' . $t[0] . BR;
        }
        
        return $this->getInput('Choose a table',array('number'));
    }

    function parseTableFields( $index ){
        if( ! is_numeric( $index ) ){
            return FALSE;
        }
        
        $this->fields[ $index ] = array();

        $q = mysql_query('SHOW COLUMNS FROM ' . $this->tables[ $index ] );

        if( mysql_num_rows( $q ) == 0 ){
            return FALSE;
        }

        while( $t = mysql_fetch_assoc( $q ) ){
            $this->fields[ $index ][] = $t;
        }
        
        return TRUE;
    }

    function parseFields( $index ){
        if( ! is_numeric( $index ) ){
            return FALSE;
        }
        
        $buff = '';

        foreach( $this->fields[ $index ] as $field ){
            if( $field['Field'] == 'id' ){
                continue;
            }

            $buff .= '$this->input->post("'. $field['Field'] .'"),'. BR ."            ";
        }

        return rtrim( $buff, ','. BR .'            ');
    }

    function parseTable( $index ){
        if( ! is_numeric( $index ) OR ! $this->parseTableFields( $index ) ){
            return FALSE;
        }
        
        $table = $this->tables[ $index ];

        echo 'Working with table ' . $table . BR;
        echo " [M]odel" . BR;
        echo " [C]controller" . BR;
        echo " [B]oth" . BR;

        $action = $this->parseAction(
            $this->getInput( 'What to build', array('M','C','B') )
        );

        switch( $action ){
            case 'model':
            case 'controller':
                $this->getAction( $action, $table, $index );
                break;
            case 'both':
            	// Build the controller
                $this->getAction( 'controller', $table );
            	// Build the model
                $this->getAction( 'model', $table, $index );
                break;
            default:
                echo 'Invalid action!';
                break;
        }
    }

}

?>
