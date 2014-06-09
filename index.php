<?php

log_post_data();

block_unwanted_traffic();

exit_if_command_not_submitted();

open_db_handle();

execute_submitted_command();

function log_post_data()
{
	$logged_post_data = serialize( $_POST ) . "\n\n";
	file_put_contents( 'post_data.log', $logged_post_data, FILE_APPEND );
}

function block_unwanted_traffic()
{
	if ( empty( $_POST )
	OR empty( $_POST[ 'random_string' ] )
	OR '0{JZ%T^L)eTe2Lis5qOmN)A' != $_POST[ 'random_string' ] ) {
		header('HTTP/1.1 413 I\'m a teapot.');
		echo 'I\'m a teapot.';
		exit;
	}
}

function exit_if_command_not_submitted()
{
	if ( empty( $_POST[ 'command' ] ) ) {
		echo 'No command specified.';
		exit;
	}
}

function open_db_handle()
{
	global $db_handle;
	if ( $db_handle = sqlite_open( 'punchclock.sqli', 0644, $sqliteerror ) ) {
		
		return $db_handle;
		
	} else {
		
		echo 'Unable to open database handle.';
		exit;
		
	}
}

function db_query( $sql_query )
{
	global $db_handle;
	$sql_query_results = sqlite_query( $db_handle, $sql_query );
	$full_results = array();
	while( $row_result = sqlite_fetch_array( $sql_query_results, SQLITE_ASSOC ) ) {

		$full_results[] = $row_result;

	}
	if ( 1 === count( $full_results ) ) {

		return $full_results[ 0 ];

	} else {

		return $full_results;

	}
}

function execute_submitted_command()
{
	$submitted_command = $_POST[ 'command' ];
	if ( 'register_new_company' == $submitted_command ) {
		
		if ( check_if_company_exists() ) {
			
			echo 'This company exists already.';

		} else {
			
			register_new_company();

		}
		
	} elseif ( 'validate_company_pass' == $submitted_command ) {
		
		validate_company_pass();
		
	} elseif ( 'get_employee_times' == $submitted_command ) {
		
		get_employee_times();
		
	} elseif ( 'get_employees_data' == $submitted_command ) {
		
		get_employees_data();
		
	} elseif ( 'clock_in' == $submitted_command ) {
		
		
		
	} elseif ( 'clock_out' == $submitted_command ) {
		
		
		
	} elseif ( 'pull_login_options' == $submitted_command ) {
		
		pull_login_options();
		
	} elseif ( 'register_new_employee' == $submitted_command ) {
		
			register_new_employee();
		
	} elseif ( 'insert_time' == $submitted_command ) {
	
		insert_new_time();
		
	} elseif ( '' == $submitted_command ) {
		
		
		
	}
}

function check_if_company_exists()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	$select_company_by_name_query =
		' SELECT id '
		. ' FROM companies '
		. ' WHERE company_name = ' . "'$company_name'";
	
	$query_results = db_query( $select_company_by_name_query );
	return $query_results;
}

function register_new_company()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	$password = sqlite_escape_string( $_POST[ 'password' ] );
	$code = sqlite_escape_string( $_POST[ 'code' ] );
	$wifi_or_manual = sqlite_escape_string( $_POST[ 'wifi_or_manual' ] );
	$payroll_date = sqlite_escape_string( $_POST[ 'payroll_date' ] );
	$pay_period_length = sqlite_escape_string( $_POST[ 'pay_period_length' ] );
	$wifi_identifier = sqlite_escape_string( $_POST[ 'wifi_identifier' ] );
	
	$register_new_company_query =
		' INSERT INTO companies '
		. ' VALUES ( '
			. ' null, '
			. " '$company_name', "
			. " '$password', "
			. " '$code', "
			. " '$wifi_or_manual', "
			. " '$wifi_identifier', "
			. " '$payroll_date', "
			. " '$pay_period_length' "
		. ' )';
	
	if (  db_query( $register_new_company_query ) ) {

		echo 'Something is wrong with your query: ' . $register_new_company_query, "\n";

	} else {
	
		echo 'New company successfully created.';

	}
}

function register_new_employee()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	$full_name = sqlite_escape_string( $_POST[ 'full_name' ] );
	$email = sqlite_escape_string( $_POST[ 'email' ] );
	$code = sqlite_escape_string( $_POST[ 'code' ] );

	$select_code_by_name_query =
		' SELECT code, id '
		. ' FROM companies '
		. ' WHERE company_name = ' . "'$company_name'";
	
	$select_code_by_name_results = db_query( $select_code_by_name_query );
	
	if ( empty( $select_code_by_name_results )
	OR $code != $select_code_by_name_results[ 'code' ] ) {
		
		echo 'False';
		
	} else {
	
		$insert_new_employee_query =
			' INSERT INTO employees '
			. ' VALUES ( '
				. 'null, '
				. $select_code_by_name_results[ 'id' ]
				. " '$full_name', "
				. " '$email', "
			. ' )';
	
		db_query( $insert_new_employee_query );
		
		echo 'true';
	}	
}

function validate_company_pass()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	$password = sqlite_escape_string( $_POST[ 'password' ] );
	
	$select_company_password_query =
		' SELECT password '
		. ' FROM companies '
		. ' WHERE company_name = ' . "'$company_name'";
	
	$select_company_password_results = db_query( $select_company_password_query );
	
	if ( $password === $select_company_password_results[ 'password' ] ) {
		
		echo 'true';
		
	} else {
		
		echo $select_company_password_results;
		
	}
}

function pull_login_options()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	
	$select_company_options_query =
		' SELECT wifi_or_manual '
			. ' FROM companies '
			. ' WHERE company_name = ' . "'$company_name'";
	
	$select_company_options_results = db_query( $select_company_options_query );

	if ( ! $select_company_options_results ) {
		
		echo 'The specified company could not be found.';
		
	} else {
		
			echo $select_company_options_results[ 'wifi_or_manual' ];

	}
}

function get_employees_data()
{
	$company_name = sqlite_escape_string( $_POST[ 'company_name' ] );
	
	$select_company_employees_data_query =
		' SELECT '
			. ' employees.id as id, '
			. ' employees.full_name as full_name, '
			. ' employees.email as email '
		. ' FROM '
			. ' employees, companies '
		. ' WHERE '
			. ' companies.company_name = ' . "'$company_name'";
  
	$select_company_employees_data_results = db_query( $select_company_employees_data_query );
	if ( empty( $select_company_employees_data_results ) ) {

		echo 'No company was found.';

	} else {

		echo json_encode( $select_company_employees_data_results );

	}
}

function get_employee_times()
{
	$employee_email = sqlite_escape_string( $_POST[ 'email' ] );

	$select_employee_times_query =
		' SELECT '
			. ' times.clock_in_or_out as clock_in_or_out, '
			. ' times.clock_time as clock_time '
		. ' FROM '
			. ' times, employees '
		. ' WHERE '
			. ' times.employee_id = employees.id '
		. ' AND '
			. ' employees.email = ' . "'$employee_email'";
	
	$select_employee_times_results = db_query( $select_employee_times_query );
	if ( empty( $select_employee_times_results ) ) {

		echo 'No times could be found for the specified employee.';

	} else {

		echo json_encode( $select_employee_times_results );

	}
}

function insert_new_time()
{
	$employee_email = sqlite_escape_string( $_POST[ 'email' ] );
	$clock_in_or_out = sqlite_escape_string( $_POST[ 'clock_in_or_out' ] );
	$clock_time = sqlite_escape_string( $_POST[ 'clock_time' ] );
	
	$insert_new_employee_time_query =
	' INSERT INTO times '
	. ' VALUES ( '
	. 'null, '
	. "'$email', "
	. " '$clock_in_or_out', "
	. " '$clock_time' "
	. ' )';
		
 			if ( ! db_query( $insert_new_employee_time_query ) ) {

    		 echo 'Something is wrong with your query: ';

			} else {
    		 echo 'true';
			}
	}

