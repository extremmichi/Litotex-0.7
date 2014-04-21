<?PHP
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2014 Michael Luckas
************************************************************
Hinweis:
Diese Software ist urheberechtlich geschuetzt.

Fuer jegliche Fehler oder Schaeden, die durch diese Software
auftreten koennten, uebernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei
duerfen NICHT entfernt und NICHT veraendert werden.
************************************************************
Released under the GNU General Public License
************************************************************
*/

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
/*ONLY FOR THE MOMEMENT UNTIL FULL UPGRADE TO MySQLi IS READY */
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


class db extends mysqli{

//      var $sql_host = "";
//      var $sql_user = "";
//      var $sql_pass = "";
//      var $sql_base = "";
        private $link_id = 0;
        public $sql_count = 0;
        public $result;

public function __construct($host, $user, $password, $database){

        $link_id = @parent::__construct($host, $user, $password, $database);

        if(mysqli_connect_error()):

            die('<b>Fehler beim Verbinden!</b><br>Connect Errno: ' . mysqli_connect_errno() . '<br>Connect Error: ' . mysqli_connect_error());

        endif;
 
    }



 // Performs a query on the database ===================================================================
    // ====================================================================================================

    public function query($query){

        $this->result = parent::query($query);
        $this->sql_count++;

        if($this->error):

            die('Fehler bei der Abfrage!<br>Query: <pre>' . $query . '</pre><br>Antwort: ' . $this->error);

        endif;

        return $this->result;

    }

// Fetch a result row as an associative and a numeric array ===========================================
    // ====================================================================================================

    public function fetch_array($givenresult){

        return $givenresult->fetch_array();

    }

 // Gets the number of rows in a previous MySQL operation =====================================
    // ====================================================================================================
      public function num_rows($result){

        $row_cnt = $result->num_rows;
        return $row_cnt;

    }

// Performs a unbuffered query on the database ======identisch mit query()  ============================================================
    // ====================================================================================================

    public function unbuffered_query($query){

        $this->result = parent::query($query, MYSQLI_USE_RESULT);

        if($this->error):

            die('Fehler bei der Abfrage!<br>Query: <pre>' . $query . '</pre><br>Antwort: ' . $this->error);

        endif;

        return $this->result;

    }

// Returns the auto generated id used in the last query ===============================================
// ====================================================================================================
    public function insert_id(){

        // The OOP method has problems with BIGINT values, therefore we use the procedural method
        return mysqli_insert_id($this);

    }


// Gets the escaped string  for use in a query=====================================
    // ====================================================================================================
      public function escape_string($string){

        return $this->real_escape_string($string);

    }

// Fetch a result row as an associative array =========================================================
    // ====================================================================================================

    public function fetch_assoc($givenresult){

        return $givenresult->fetch_assoc();

    }

 // Returns the current row of a result set as an object ===============================================
    // ====================================================================================================

    public function fetch_object($givenresult){

        return $givenresult->fetch_object();

    }


    // Get a result row as an enumerated array ============================================================
    // ====================================================================================================

    public function fetch_row($givenresult){

        return $givenresult->fetch_row();

    }


public function number_of_querys() {
                return $this->sql_count ;
        }



public function __destruct(){

        if($this->link_id):

            $this->close();

        endif;
    }

}

//Eof//


