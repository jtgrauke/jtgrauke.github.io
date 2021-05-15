---
#Empty frontmatter necessary
layout: none
---
<?
require_once('../../private/php/bootstrap.php');

$status = 'ERROR';

if( !isset($_POST['age']) && isset($_POST['email']) && isset($_POST['token1']) && isset($_POST['token2']) ){
	if ( match_anti_spam_tokens($_POST['token1'], $_POST['token2']) ){
		$email = $_POST['email'];
		$slack_client = new Slack(); 
        
	    try{
	        date_default_timezone_set('US/Pacific');
	        
	        //Try to send them a mailchimp confirmation email
            $mailchimp_api = new MailChimp();
            $status = $mailchimp_api->list_subscribe( $email );
            
            if( $mailchimp_api->error ){
                try{
                	trigger_error($mailchimp_api->error);
                    send_error_email('Newsletter signup mailchimp error', 'Failed to add to Mailchimp with HTTP code: ' . $mailchimp_api->error  . ', $_POST: ' . print_r($_POST, true));
                    $slack_client->post_message($email . ' did not really sign up for the newsletter beacuse of a Mailchimp error', 'demosignups', 'Newsletter Signup', ':exclamation:');
                }
                catch(Exception $e){}
            } 
            if( $status == 200 ){
        		$slack_client->post_message($email . ' signed up for the newsletter', 'demosignups', 'Newsletter Signup', ':newspaper:');    	
            }
	    }
	    catch(Exception $e){
	        try{
	            send_error_email('Newsletter signup error', 'Exception thrown... message: ' . $e->getMessage() . print_r($_POST, true));
	            $slack_client->post_message($email . ' did not really sign up for the newsletter beacuse of an exception', 'demosignups', 'Newsletter Signup', ':exclamation:');
	        }
	        catch(Exception $e){}
	    }
	}
}    

echo $status;
?>