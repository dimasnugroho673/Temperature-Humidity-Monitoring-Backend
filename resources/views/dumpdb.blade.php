<?php 

define("DB_HOST", "localhost:3306");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "dimasnugroho673");
define("DB_NAME", "aman_db");

$con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die ("Error : ");
if(mysqli_connect_errno($con)) {
  echo "Failed to connect MySQL: " .mysqli_connect_error();
} else {
  //If you want to export or backup the whole database then leave the $table variable as it is
  //If you want to export or backup few table then mention the names of the tables within the $table array like below
  //eg, $tables = array("wp_commentmeta", "wp_comments", "wp_options");

  $tables = array();
    $backup_file_name = DB_NAME.".sql";
    backup_database($con, $tables, $backup_file_name);
  }
?>

<?php
function backup_database($con, $tables = "", $backup_file_name) {

  if(empty($tables)) {
    $tables_in_database = mysqli_query($con, "SHOW TABLES");
    if(mysqli_num_rows($tables_in_database) > 0) {
      while($row = mysqli_fetch_row($tables_in_database)) {
        array_push($tables, $row[0]);
      }
    } 
  } else {
    // Checking for any table that doesn't exists in the database
    $existed_tables = array();
    foreach($tables as $table) {
      if(mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE '".$table."'")) == 1) {
        array_push($existed_tables, $table);
      }
    }
    $tables = $existed_tables;
  }


  $contents = "--\n-- Database: `".DB_NAME."`\n--\n-- --------------------------------------------------------\n\n\n\n";

  foreach($tables as $table) {
    $result        = mysqli_query($con, "SELECT * FROM ".$table);
    $no_of_columns = mysqli_num_fields($result);
    $no_of_rows    = mysqli_num_rows($result);

    //Get the query for table creation
    $table_query     = mysqli_query($con, "SHOW CREATE TABLE ".$table);
    $table_query_res = mysqli_fetch_row($table_query);

    $contents .= "--\n-- Table structure for table `".$table."`\n--\n\n";
    $contents .= $table_query_res[1].";\n\n\n\n";

    /**
     *	$insert_limit -> Limits the number of row insertion in a single INSERT query. 
     *  				 Maximum 100 rowswe will insert in a single INSERT query.
     *  $insert_count -> Counts the number of rows are added to the INSERT query. 
     *                   When it will reach the insert limit it will set to 0 again.
     *  $total_count  -> Counts the overall number of rows are added to the INSERT query of a single table.
     */
    $insert_limit = 100;
    $insert_count = 0;
    $total_count  = 0;


    while($result_row = mysqli_fetch_row($result)) {

      /**
       * For the first time when $insert_count is 0 and when $insert_count reached the $insert_limit 
       * and again set to 0 this if condition will execute and append the INSERT query in the sql file. 
       */
      if($insert_count == 0) {
        $contents .= "--\n-- Dumping data for table `".$table."`\n--\n\n";
        $contents .= "INSERT INTO ".$table." VALUES ";
      }

      //Values part of an INSERT query will start from here eg. ("1","mitrajit","India"),
      $insert_query = "";
      $contents .= "\n(";
      for($j=0; $j<$no_of_columns; $j++) {
        //Replace any "\n" with "\\n" escape character.
        //addslashes() function adds escape character to any double quote or single quote eg, \" or \'
        $insert_query .= "'".str_replace("\n","\\n", addslashes($result_row[$j]))."',";
      }
      //Remove the last unwanted comma (,) from the query.
      $insert_query = substr($insert_query, 0, -1)."),";

      /*
       *	If $insert_count reached to the insert limit of a single INSERT query
       *  or $insert count reached to the number of total rows of a table
       *  or overall total count reached to the number of total rows of a table
       *  this if condition will exceute.
       */
      if($insert_count == ($insert_limit-1) || $insert_count == ($no_of_rows-1) || $total_count == ($no_of_rows-1)) {
        //Remove the last unwanted comma (,) from the query and append a semicolon (;) to it
        $contents .= substr($insert_query, 0, -1);
        $contents .= ";\n\n\n\n";
        $insert_count = 0;
      } else {
        $contents .= $insert_query;
        $insert_count++;
      }

      $total_count++;				
    }	
  }


  //Set the HTTP header of the page.
    header('Content-Type: application/octet-stream');   
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"".$backup_file_name."\"");  
    echo $contents; exit;
}
?>