<?php
/**
 * IPB SDK - Version 2.0
 * English Language Pack
 *
 * @author Cow <khlo@global-centre.com>
 * @date   25/04/03
 */

// General
$lang['sdk_membersonly']       = "This feature is only avaliable to registered members.";
$lang['sdk_noperms']           = "You do not have the required permissions for this action.";
$lang['sdk_floodcontrol']      = "Flood Prevention - Please wait another '%s' seconds before attempting to post.";

// Categories
$lang['sdk_cat_notexist']      = "Category does not exist";

// Custom Profile Fields
$lang['sdk_cfcantedit']        = "Cannot edit Custom Profile Field '%s'.";
$lang['sdk_cfnotexist']        = "Custom Profile Field '%s' does not exist.";
$lang['sdk_cfmustfillin']      = "Custom Profile Field '%s' must be filled in.";
$lang['sdk_cfinvalidvalue']    = "Invalid Value";

// Email
$lang['sdk_email_template']    = "<#MESSAGE#>";

// Forums
$lang['sdk_forum_notexist']    = "Forum does not exist.";
$lang['sdk_forum_noname']      = "You did not input a forum name.";

// Members
$lang['sdk_badmemid']          = "Invalid User ID";
$lang['sdk_badmempw']          = "False or Invalid User Password";
$lang['sdk_cfmissing']         = "One or more required custom profile fields were not specified.";
$lang['sdk_cflength']          = "One or more required custom profile fields were too long.";
$lang['sdk_acc_user']          = "The username specified is not valid.";
$lang['sdk_acc_pass']          = "The password specified is not valid.";
$lang['sdk_acc_email']         = "The e-mail address specified is not valid.";
$lang['sdk_acc_taken']         = "The username or email specified is taken and is currently being used.";
$lang['sdk_groupicon']         = "Group Icon";
$lang['sdk_memphoto']          = 'Member Photo';
$lang['sdk_badkey']	       = 'Key doesn\'t exist';

// Login
$lang['sdk_login_nofields']    = "Please specify the username and password.";
$lang['sdk_login_length']      = "The username or password were too long.";
$lang['sdk_login_wrongpass']   = "The password was incorrect.";
$lang['sdk_login_memid']       = "No Member ID.";
$lang['sdk_login_nomember']    = "The member does not exist.";

// Profiles
$lang['sdk_sig_toolong']       = "Signature was too long.";

// PM
$lang['sdk_pm_no_recipient']   = "No Recipient Specified.";
$lang['sdk_pm_title']          = "Invalid Message Title.";
$lang['sdk_pm_message']        = "Invalid Message.";
$lang['sdk_pm_mem_notexist']   = "The member does not exist.";
$lang['sdk_pm_mem_disallowed'] = "The member specified cannot use the PM system.";
$lang['sdk_pm_mem_full']       = "The member's inbox is full.";
$lang['sdk_pm_mem_blocked']    = "The member has blocked you.";
$lang['sdk_pm_cclimit']        = "You cannot CC messages to so many users.";
$lang['sdk_pm_rec_disallowed'] = "A recepient specified cannot use the PM system.";
$lang['sdk_pm_rec_full']       = "A recepient's inbox is full.";
$lang['sdk_pm_rec_blocked']    = "A recepient has blocked you.";
$lang['sdk_pm_folder_noexist'] = "Folder does not exist.";
$lang['sdk_pm_folder_tnoexist']= "Folder does not exist.";
$lang['sdk_pm_folder_norem']   = "Folder cannot be removed.";
$lang['sdk_pm_msg_no_move']    = "Could not move message.";

// Polls
$lang['sdk_poll_alreadyvoted'] = "You have already voted in this poll.";
$lang['sdk_poll_invalid_vote'] = "Invalid Vote.";
$lang['sdk_poll_noexist']      = "Poll does not exist.";
$lang['sdk_poll_invalid_opts'] = "You must specify between 2 and %s  options.";
$lang['sdk_poll_invalid_questions'] = "You must specify between 1 and %s  questions.";

// Posts
$lang['sdk_posts_notexist']   = "Post does not exist.";

// Search
$lang['sdk_search_noforum']    = "You must search at least one forum.";
$lang['sdk_search_noresults']  = "No results were found.";

// Skins
$lang['sdk_skin_notexist']     = "Skin does not exist.";

// Topics
$lang['sdk_topics_notitle']    = "You did not enter a topic title.";
$lang['sdk_topics_notexist']   = "Topic does not exist.";

$lang = array(
	
	//2.3
	'help_alt'						=> 'Help',

	//RC2
	'insert_prefix'					=> "Insert",
	'bbcodeloader_insert'			=> "Insert",
	'bbcodeloader_title'			=> "Insert Custom BBCode",
	'bbcodeloader_example'			=> "Example",
	'bbcodeloader_option'			=> "Option Text",
	'bbcodeloader_content'			=> "Content Text",
	'bbcodeloader_ok'				=> "OK",
	'bbcodeloader_cancel'			=> "Cancel",

	//2.2
	'js_rte_lite_code'				=> "Wrap in code tags",
	'js_rte_lite_quote'			    => "Wrap in quote tags",
	'js_rte_lite_link'              => "Insert Link",
	'js_rte_lite_img'               => "Insert Image",
	'js_rte_lite_email'             => "Insert Email Link",
	'toggle_side_panel'				=> "Toggle Side Panel",
	'emos_show_all'					=> "Show All",
	'emos_show_prev'				=> "Previous",
	'emos_show_next'				=> "Next",
	'ed_quick_access'				=> "Quick Access",
	'js_tt_emoticons'				=> "Emoticons",
	'js_tt_unlink'					=> "Unlink",
	'js_tt_sub'						=> "Sub-script",
	'js_tt_sup'						=> "Super-script",
	'js_rte_quote'					=> "Wrap as <strong>Quote</strong>",
	'js_rte_code'					=> "Wrap as <strong>Code</strong>",
	'js_tt_spellcheck'				=> "Spellcheck",
	'js_tt_switcheditor'			=> "Switch between standard and rich text editor",

	'js_rte_link'          			=> "Insert <strong>Link...</strong>",
	'js_rte_unlink'        			=> "<strong style='color:red'>Unlink</strong> Text...",
	'js_rte_image'         			=> "Insert <strong>Image...</strong>",
	'js_rte_email'        			=> "Insert <strong>Email...</strong>",
	'js_rte_erroriespell'   		=> "ieSpell not detected.  Click Ok to go to download page.",
	'js_rte_errorliespell'  		=> "Error Loading ieSpell: Exception ",
	'js_rte_optionals'              => "Enter the optional arguments for this tag",

	'the_max_length'				=> "0",
	'override'						=> "0",

	'js_tt_noformat'      			=> "Remove Formatting",
	'js_tt_htmlsource'				=> "Toggle HTML Source",
	'js_tt_insert_item'			    => "Insert Special Item",

	//2.1.2

	'box_font'            			=> "Fonts",
	'box_size'            			=> "Sizes",
	'js_tt_undo'          			=> "Undo",
	'js_tt_redo'          			=> "Redo",
	'js_tt_smaller'					=> "Resize smaller",
	'js_tt_larger'					=> "Resize larger",
	'js_tt_bold'          			=> "Bold",
	'js_tt_italic'        			=> "Italic",
	'js_tt_underline'     			=> "Underline",
	'js_extra_formatting'			=> "Extra Formatting",
	'js_text_formatting'			=> "Text Formatting",
	'js_tt_font_col'      			=> "Text Color",
	'js_tt_back_col'      			=> "Background Color",
	'js_tt_indent'        			=> "Indent",
	'js_tt_outdent'       			=> "Outdent",
	'js_tt_left'          			=> "Align Left",
	'js_tt_center'        			=> "Align Center",
	'js_tt_right'         			=> "Align Right",
	'js_tt_jfull'					=> "Justify Full",
	'js_tt_list'          			=> "Insert List",


	'button_init'					=> "Initializing...",
	'jsfile_error'					=> "Error!",
	'jsfile_error_c'				=> "Error:",
	'jsfile_mywebpage'				=> "My Webpage",
	'jsfile_alert1'					=> "Some revisions of Mozilla/Firefox do not support programatic ",
	'jsfile_alert2'					=> "access to cut/copy/paste functions, for security reasons.  ",
	'jsfile_alert3'					=> "Your browser is one of them.  Please use the standard key combinations:",
	'jsfile_alert4'					=> "CTRL-X for cut, CTRL-C for copy, CTRL-V for paste.",
	'jsfile_highlight'				=> "You must highlight some text before making it a link",

	'js_forced_change'   			=> "You have set 'Rich Text Editor' as your editor but your browser isn't compatible with it. The standard editor has been used instead.",
	'js_tt_closeall'     			=> "Close All Tags",

	'js_tt_strike'        			=> "Strikethrough",

	'js_tt_copy'          			=> "Copy",
	'js_tt_paste'         			=> "Paste",
	'js_tt_cut'           			=> "Cut",
	'js_tt_code'          			=> "Code",


	'js_bbeasy_on'      			=> 'Guided Mode Enabled',
	'js_bbeasy_off'     			=> 'Guided Mode Off',
	'js_bbeasy_toggle'  			=> 'Click to toggle guided mode',

	'remove_attach'     			=> "Are you sure you wish to remove this attachment?",

	'js_close_all_tags'  			=>  "Close all Tags",
	'js_tag_list'        			=>  "Enter a list item. Click 'cancel' or leave blank to end the list",
	'jscode_text_enter_url'			=>	"Enter the complete URL for the hyperlink",
	'jscode_text_enter_email'		=>	"Enter the email address",
	'jscode_text_code'				=>	"Usage: [CODE] Your Code Here.. [/CODE]",
	'jscode_error_no_title'			=>	"You must enter a title",
	'jscode_text_quote'				=>	"Usage: [QUOTE] Your Quote Here.. [/QUOTE]",
	'jscode_error_no_url'			=>	"You must enter a URL",
	'jscode_text_enter_image'		=>	"Enter the complete URL for the image",
	'jscode_error_no_width'			=>	"You must enter a width",
	'jscode_text_enter_url_name'	=>	"Enter the title of the webpage",
	'jscode_error_no_email'			=>	"You must enter an email address",
	'js_used'					    =>	"So far, you have used",
	'js_max_length'					=>	"The maximum allowed length is",
	'js_post'					    =>	"Post",
	'js_no_message'					=>	"You must enter a message to post!",
	'js_current'					=>	"Current Characters",
	'js_characters'					=>	"characters",
	'msg_no_title'                  => "You must enter a message title",
	'js_check_length'			    =>	"Check Post Length",

	'hb_open_tags' 					=> "Open Tags",


	'js_text_to_format'  			=> "Enter the text to be formatted",
	);
?>