m<?php
define('BASEPATH','../');
define('DS',DIRECTORY_SEPARATOR);

// TODO: find a better place to put the include :(
include( BASEPATH . '..'. DS .'helpers'. DS .'inflector_helper.php');

class ActiveScaffold {
    var $actions = array('M','MODEL','C','CONTROLLER');
    var $conn;
    var $args;
    var $reserved = array(
        'Controller', 'CI_Base', '_ci_initialize', '_ci_scaffolding', 'index'
    );

    function run( $args ){
        array_shift( $args );

        $this->args = $args;

        switch( count( $this->args ) ){
            case 2:
                $this->getAction(
                    $this->parseAction( $this->args[0] ), $this->args[1]
                ); break;

            case 1:
                
                break;

            default:
                echo 'menu';
                break;
        }
    }

    function getAction( $type, $name = FALSE ){
        if( ! $type ){
            // TODO: Redirect to menu
            echo "Invalid action!";
            return FALSE;
        }

        $name = $this->parseName( $name );

        if( $this->getInput( 'Build a '. $type .' called "'. $name .'"', array('y','n') ) == 'n' ){
            // TODO: Redirect to menu
            return FALSE;
        }

        // TODO: validar se a resposta e boleana
        if( $this->save( $name, $type ) === TRUE ){
            echo ucwords( $type ) .' salvo!';
        }
        
        
    }
    
    function save( $name, $type ){
        $dir = BASEPATH . $type . 's';

        if( ! is_dir( $dir ) ){
            return FALSE;
        }

        // TODO: Colocar a validacao antes
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
        }
    }

    // TODO: mudar o retorno para array com nome do arquivo e conteudo
    function parseTemplate($name, $template){
        $buff = @file_get_contents( $template );

        if( $buff === FALSE ){
            return FALSE;
        }

        $buff = str_replace( '{name}', ucwords( $name ), $buff );

        $buff = str_replace( '{fileName}', strtolower( $name ), $buff );

        return $buff;
    }

    function parseName( $name ){
        return strtolower( $name );
    }

    function help(){
        echo " ActiveScaffold Help\n";
        echo " -------------------------\n";
        echo " [M]odel\n";
        echo " [C]controller\n";
        echo " -------------------------\n\n";
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
}

?>
