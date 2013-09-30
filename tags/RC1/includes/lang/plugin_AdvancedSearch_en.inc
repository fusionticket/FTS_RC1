<?php
// Language file updated 13/04/13 by Lxsparks

// Global Search and Edits Tab in 'Admin -> Search'
 define('form_title_main','Main Search form');
 define('form_title_main_notes','Each section of the search sections provides ways to search for customers and then to edit their 
 data.');

 // General definitions
 define('N/A','N/A');
 define('Yes','Yes');
 define('No','No');

 // Search Form Block Titles
 define('global_search_block_title_address','Search: Address');
 define('global_search_block_title_name','Search: Name');
 define('global_search_block_title_phone', 'Search: Phone or Fax');
 define('global_search_block_title_email','Search: Emails');
 define('global_search_block_title_combine','Search: Duplicate customers');
 define('global_search_block_title_activity','Search: Customer activity');
 define('global_search_block_title_admin','Admin Section');
 
 // Search Form
 define('global_search_title_street','Search by Street');
 define('global_search_notes_street','The wildcard \'?\' can be used (e.g. ?street, or ? The High Street)');
 define('global_search_title_city','Search by Town/City');
 define('global_search_notes_city','The wildcard \'?\' can be used.');
 define('global_search_title_state','Search by County');
 define('global_search_notes_state','The wildcard \'?\' can be used.');
 define('global_search_title_zip','Search by Postcode');
 define('global_search_notes_zip','The wildcard \'?\' can be used (e.g. NW?, or GL? NN');
 define('global_search_title_zip_range','Search Postcodes range');
 define('global_search_notes_zip_range','Search for postcdes between two values - again the \'?\' wildcard can be used.');
 define('global_search_zip_start','Postcodes between:');
 define('global_search_zip_end','and:');
 define('global_search_title_country', 'Search by Country');
 define('global_search_notes_country', 'Use the option to search for Customers who are from or not from the selected country');
 define('global_search_include_country', 'Find Customers from the selected country?');
 define('global_search_title_phone', 'Search by Customer phone number');
 define('global_search_notes_phone', 'The wildcard \'?\' can be used to search on numbers e.g. \'0117?\' for numers starting with the Bristol area code, 
	\'?208?\' for numbers that contain \'208\' or \'?111\' for numbers that end with \'111\'.');
 define('global_search_title_fax', 'Search by Customer fax number');
 define('global_search_notes_fax', 'The wildcard \'?\' can be used.');
 define('global_global_search_block_title_email', 'Search by email');
 define('global_search_notes_email', 'Use the option to search for Customers whose email includes the search criteria or those that do not');
 define('global_search_duplicate_email','Search for duplicate emails');
 define('global_search_include_email', 'Match the criteria?');
 define('global_search_title_surname','Search by Customer Surname');
 define('global_search_notes_surname','Search for Customers by surname <br />Use \'?\' as a wildcard at one or both ends (e.g. \'?sm\', \'?mit?\' or \'th?\')');
 define('global_search_user_surname','Surname:');
 define('global_search_user_surname_start','are between:');
 define('global_search_user_surname_end','and:');
 define('global_search_title_ticketsBetween','Tickets bought between');
 define('global_search_notes_ticketsBetween','Seach for (Member) customers who have bought tickets between two dates.');
 define('global_search_tickets_between','Tickets between:');
 define('global_search_title_event', 'Customer\'s who have tickets ordered for an event');
 define('global_search_notes_event', 'Select an event from the drop-down list');
 define('global_search_title_activity','Search Customers by their Activity and/or Status');
 define('global_search_notes_activity','If no options are selected then ALL customer records will be pulled from the database');
 define('global_search_opt_active','Customer has activated their account');
 define('global_search_opt_user_ticket_status','Customer currently has tickets');
 define('global_search_opt_user_login_last','Customer has not logged on since');
 define('global_search_title_global_posMerge','Duplicate customers search: Members, Guests & POS');
 define('global_search_notes_global_posMerge','Search for duplicate users who have entries as Members AND Guests or POS customers');
 define('global_search_title_dbCheck_orders', 'Customer Ticket Consistency Check');
 define('global_search_notes_dbCheck_orders', 'Checks and updates Customer records to show number of actual tickets and orders currently on the system. By default the system 
		displays a running total of total tickets/orders held by the Customer. <br/><br/>Customers cannot be removed from the system though unless they have zero orders/tickets so 
		use this feature to update records if you want to remove old users from the database.<br/><br/>Warning - this cannot be undone.');


 // Search results table
 define('global_table_id','ID');
 define('global_table_name','Name');
 define('global_table_city','City');
 define('global_table_state','County');
 define('global_table_address', 'Address');
 define('global_table_address1', 'Address #1');
 define('global_table_country', 'Country');
 define('global_table_zip','Postcode');
 define('global_table_phone','Phone Number');
 define('global_table_fax','Fax Number');
 define('global_table_email', 'Email');
 define('global_table_joined','Joined');
 define('global_table_last_visit','Last Visited');
 define('global_table_qty','QTY');
 define('global_table_status','Status');
 define('global_table_results_header','Search returned');
 define('global_table_customers','customers');
 define('global_table_ref', 'Ref');
 define('global_table_data', 'Data Field');
 define('global_table_info', 'Information');
 define('global_table_select', 'Select to update');
 define('global_table_emailing', 'Emailing');
 define('global_table_notes_emailing', 'Use the option button to select how the customer emails are included.  The emails won\'t be show on this page.');
 define('global_table_title_ordered_events', 'Customer\'s who have booked for');
 define('global_table_ttl_orders', 'No. of Orders');
 define('global_table_title_customer_address','Searched on street that matched');
 define('global_table_title_customer_city','Searched on Town/City that matched');
 define('global_table_title_state', 'Customer\'s who live in the county of');
 define('global_table_title_customer_zip','Customers that matched the postcode');
 define('global_table_title_customer_zip_range','Searched for Postcodes in the range');
 define('global_table_title_customer_surname_btwn','Surnames that are between the letters');
 define('global_table_title_countryLike', 'Countries that matched');
 define('global_table_title_countryNot', 'Countries that did not match');
 define('global_table_title_customer_surname','Surname which matches');
 define('global_table_title_customer_tickets_between','Customers who bought tickets between');
 define('global_table_title_emailLike', 'Emails that match');
 define('global_table_title_emailNot', 'Emails that did not match');
 define('global_table_title_customer_activated_both','Account activated: n/a');
 define('global_table_title_customer_activated_yes','Account activated: yes');
 define('global_table_title_customer_activated_no','Account activated: no');
 define('global_table_title_customer_tickets_both','Has tickets: n/a');
 define('global_table_title_customer_tickets_yes','Has tickets: yes');
 define('global_table_title_customer_tickets_none','Has tickets: no');
 define('global_table_title_customer_login_req','User has not logged on since');
 define('global_table_title_customer_login_notreq','Last login not selected');
 define('global_table_page_rows', 'Results Per Page: ');
 define('global_table_results_header_edit', 'Editing'); 
 
 // Functions
 define('fnc_search_term','Searched on');
 define('fnc_update_title_updated','Update success!');
 define('fnc_update_notes_updated','Customer records updated');
 define('fnc_update_title_notupdated','No records were updated this time');
 define('fnc_update_notes_notupdated','We\'re any records altered?');
 define('fnc_delete_title_updated', 'Success!');
 define('fnc_delete_notes_updated', 'Number of customers removed');
 define('fnc_delete_title_notupdated', 'Failure');
 define('fnc_delete_notes_notupdated', 'No customers were deleted this time');
 define('fnc_member_cust_rec','Member customer record:');
 define('fnc_pos_cust_rec','POS customer record:');
 define('fnc_replaced_by','replaced by');
 define('fnc_dbCheck_orders','Update customer records');
 define('fnc_form_notes_global_edit_manual','Manually edit the data, be-warned once updated there is no \'undo\' button!');
 define('fnc_form_notes_global_edit_global','Data entered will replace the information on ALL the customers currently selected only if the field is \'selected\' (if 
		the field is selected but left blank then this will effectively delete any information stored for the customers).<br /><br />Postcodes and phone numbers will be formated to the standard of the current 
		country that is listed even if it\'s not selected.');
 define('fnc_search_notes_edit_format','These are the results after automatic clean up - further editing can be done by using the manual edit function');
 
 // Forms
 define('frm_posMerge_select_user','Combine POS/Guest details into Member records?');
 define('frm_notes_global_posMerge_update','Note: A customer may register using an email already used by a Guest and/or POS User but not the otherway round.<br /><br />
 Tickets of POS and Guest customers will be moved to Member records.  <br /><br /> ***WARNING***  Backup database before proceeding with merging of any records.  POS/Guest records will be deleted!');
 define('frm_table_results_header_edit','Editing');
 
 // Email Form
 //define('email_from', 'From');
 //define('email_to', 'To');
 define('email_cc', 'CC');
 define('email_bcc', 'BCC');
 define('email_title', 'Title');
 define('email_msg', 'Message');
 define('email_option', 'Include selected Customers');
 
 // Buttons
 define('btn_search','Search');
 define('btn_reset','Reset');
 define('btn_manual_edit','Manual Edit');
 define('btn_format_data','Format Data');
 define('btn_global_edit','Global Edit');
 define('btn_merge','Merge Data');
 define('btn_cancel','Cancel');
 define('btn_global_email', 'Email');
 define('btn_global_delete', 'Delete');
 define('btn_form_cancel','Cancel');
 define('btn_update_data','Update Data');
 define('btn_email_users', 'Email Customers');
 
 // Confirmation Messages
 define('confirm_sure','Are you sure you want to update the Customer record\/s?');
 define('confirm_cancel','Are you sure you want to cancel?');
 define('confirm_email','Are you sure you want to send email?');
 define('confirm_delete_user', 'Delete this customer?');
 define('confirm_email_user', 'Email this customer?');
 
 // Warning Messages
 define('msg_email_find_fail', 'Failed to find emails in database');
 define('msg_dbCheck_orders_fine', 'Customer order and ticket details are consistant');
 define('msg_user_orders_seats_update_failed', 'Failed to update customer order/ticket details');
 define('msg_users_updated_total','Number of users updated: ');
 define('msg_choice_one_fields','At least one option must be selected!');
 define('msg_search_choice_two_fields', 'At least two options should be selected!');
 define('msg_update_failed', 'Update failed');
 define('msg_no_result', 'No results!  Nothing matched your criteria');
 define('msg_search_AZ_aBiggerZ', 'The first letter must be lower than the second!');
 define('msg_search_type_not_found', 'Search type not found'); 

 
 // No Longer Used
 //define('?','?');
 //define('dbc_guest_cust_rec','Guest customer record:');
 //define('search_title_global_cleanCustomerData','Clean Customer & mailing information');
 //define('search_notes_global_cleanCustomerData1','Search for Customer surnames that:');
 //define('search_notes_global_cleanCustomerData2','Search for Customers by surname - use \'?\' as a wildcard at one or both ends');
 //define('global_search_title_global_posMerge_update','Duplicate customers (Members, Guests & POS)');
 //define('posMerge_email','Check on emails');
 //define('global_table_title_customer_city', 'Customer\'s who live in');
 //define('global_table_title_customer_zip', 'Customer\'s Postcode');
 //define('global_table_title_customer_address', 'Customer\'s address');
 //define('global_table_title_customer_surname', 'Customer\'s with the surname');
 
 //define('global_tab','Global Search/Edit');
 //define('search_title_global','Search Customer records');
 //define('search_notes_global','Leaving all fields empty will pull every customer record who are Members, Guests and/or POS customers');
 
 ?>