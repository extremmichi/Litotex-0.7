<?PHP
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich geschützt.

Für jegliche Fehler oder Schäden, die durch diese Software
auftreten könnten, übernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei
dürfen NICHT entfernt und NICHT verändert werden.
************************************************************
Released under the GNU General Public License
************************************************************
*/
class db {

	var $sql_host = "";
	var $sql_user = "";
	var $sql_pass = "";
	var $sql_base = "";
	var $link_id = 0;
	var $sql_count = 0;

	function db($host,$user,$pass,$base) {
		$this->sql_host=$host;
		$this->sql_user=$user;
		$this->sql_pass=$pass;
		$this->sql_base=$base;
		$this->connect();
	}

	function connect() {
		$this->link_id=@mysql_connect($this->sql_host,$this->sql_user,$this->sql_pass);
		if(!$this->link_id) $this->error("False link == Error to connect the database");
		$selecting_base=@mysql_select_db($this->sql_base);
		if(!$selecting_base) $this->error("Flase base == Error to select the database");
	}

	function query($query_string) {
		$selecting_query=@mysql_query($query_string);
		$this->sql_count ++;
		if(!$selecting_query) $this->error("False query == $query_string");
		return $selecting_query;
	}

	function fetch_array($result_string) {
		$selecting_result=@mysql_fetch_array($result_string);
		return $selecting_result;
	}

	function num_rows($result_string) {
		$selecting_result=@mysql_num_rows($result_string);
		return $selecting_result;
	}

	function unbuffered_query($query_string) {
		$selecting_result=mysql_unbuffered_query($query_string);
		if(!$selecting_result) $this->error("False query == $query_string");
		return $selecting_result;
	}

	function insert_id() {
		$query_id=@mysql_insert_id($this->link_id);
		return $query_id;
	}

	function escape_string($string){
		return mysql_real_escape_string($string);
	}

	function number_of_querys() {
		return $this->sql_count ;
	}


	function error($error) {
		echo ("<title>Error by Base - $this->appname</title>");
		echo ("Error: <b>$error</b><br>\n");
		echo ("SQL-Error: ".mysql_error()."<br>\n");
		echo ("Derzeit gibt es Datenbank Probleme, bitte haben Sie etwas gedult.");
		exit();
	}

}
