<?php
/**
 * EvoCheck language file
 *
 * @version 0.3
 * @date 2018/02/02
 * @author Deesen
 *
 * @language English
 *
 * Please commit your language changes on Transifex (https://www.transifex.com/modx/modx-evolution-extras/) or on GitHub (https://github.com/extras-evolution/evocheck).
 */


$_lang["navbar_search"] = 'Search';
$_lang["navbar_server"] = 'Server';
$_lang["navbar_check_integrity"] = 'Integrity-Check';
$_lang["navbar_check_indexhtm"] = 'Create index.html';
$_lang["navbar_logout"] = 'Logout';
$_lang["dashboard_critical_events"] = 'Plugins assigned to critical Events';
$_lang["dashboard_modx_config_checkup"] = 'MODX Config Checkup';
$_lang["dashboard_check_files_on_login"] = 'Check File-Changes on Login';
$_lang["changes_found_in"] = 'Changes found in';
$_lang["no_changes_found"] = 'No changes found';
$_lang["helpful_resources"] = 'Helpful Resources';
$_lang["element_disabled"] = '(disabled)';
$_lang["system_setting"] = 'System Setting';
$_lang["path"] = 'Path';
$_lang["status"] = 'Status';
$_lang["are_you_sure"] = 'Are you sure?';
$_lang["bytes"] = 'Bytes';

$_lang["indexhtm_default"] = "&lt;h2&gt;Unauthorized access&lt;/h2&gt;\nYou're not allowed to access file folder";
$_lang["btn_update"] = 'Update';
$_lang["btn_create_indexhtm"] = 'Create index.html';
$_lang["indexhtm_directories_containing"] = 'Directories containing index.htm';
$_lang["indexhtm_title"] = 'Create index.htm';
$_lang["indexhtm_introtext"] = 'If for any reason (like cheap webhosters) server-setting "Directory Listing" is set to "Options +Indexes", you can prevent listing of your directory-contents by placing a index.html-file with this tool.';
$_lang["scan_results"] = 'Scan Results';
$_lang["indexhtm_files_found"] = 'index.html files found';
$_lang["indexhtm_files_missing"] = 'index.html files missing';
$_lang["excluded_directories"] = 'Excluded Directories';
$_lang["indexhtm_content"] = 'Content of index.html';
$_lang["indexhtm_create_options"] = 'Options';
$_lang["indexhtm_option_add"] = 'Add index.html';
$_lang["indexhtm_option_add_msg"] = 'Do not overwrite existing files.';
$_lang["indexhtm_coption_overwrite"] = 'Overwrite';
$_lang["indexhtm_coption_overwrite_msg"] = 'Overwrite existing files.';
$_lang["indexhtm_option_remove"] = 'Remove';
$_lang["indexhtm_option_remove_msg"] = 'Remove all index.html files';
$_lang["indexhtm_option_filter_size"] = 'Ignore Files';
$_lang["indexhtm_filter_size_msg"] = 'Files bigger than this filesize will be ignored / not get modified.';
$_lang["indexhtm_files_added"] = 'Files added';
$_lang["indexhtm_files_altered"] = 'Files altered';
$_lang["indexhtm_files_removed"] = 'Files removed';
$_lang["indexhtm_files_duplicates_removed"] = 'Duplicates removed (.htm / .html)';
$_lang["indexhtm_files_error"] = 'Errors';
$_lang["indexhtm_files_skipped"] = 'Files skipped';

$_lang["integrity_title"] = 'Integrity-Check';
$_lang["integrity_introtext"] = 'With this tool you can create integrity-images, which are a collection of checksums (SHA-1), that can be used to find any files that might have been changed afterwards, i.e. due to a hack. So we recommend to create such an image for later comparison, once your website is finished and going live. Alternatively you can download integrity-images of each EVO-version <a href="https://github.com/extras-evolution/evocheck-integrity" target="_blank">here</a> to compare your installation against a default one.';
$_lang["btn_create_integrity"] = 'Create';
$_lang["btn_compare_integrity"] = 'Compare';
$_lang["integrity_compare"] = 'Compare Integrity-Image';
$_lang["integrity_compare_intro"] = 'Choose the image you want to compare against your actual installation.';
$_lang["integrity_images_directory"] = 'Directory to upload / download Integrity-Images';
$_lang["integrity_create"] = 'Create Integrity-Image';
$_lang["integrity_create_intro"] = 'Set the options for the image to create. If you want to exclude directories, please manually enter paths relative to root (example: assets/cache).';
$_lang["integrity_create_filename"] = 'Filename of Integrity-Image';
$_lang["integrity_excluded_directories"] = 'Directories to be excluded from Image';
$_lang["integrity_create_success"] = 'Image has been successfully created. It is advised to download this image and store a copy on your local computer.';
$_lang["integrity_create_error"] = 'Any error occured, the image-file could not be written.';
$_lang["integrity_file_exists"] = 'A file already exists with this name. Please choose a different name.';
$_lang["integrity_compare_result_intro"] = 'The image has been successfully compared to your installation. Find the results below.';
$_lang["integrity_compare_result_error"] = 'The image-file seems corrupt or could not be read.';
$_lang["integrity_compare_no_result"] = 'No results.';
$_lang["integrity_alert_choose_image"] = 'Please choose an image to compare.';

$_lang["integrity_compare_files_integer"] = 'Files integer';
$_lang["integrity_compare_files_changed"] = 'Files changed';
$_lang["integrity_compare_files_notfound"] = 'Files not found/deleted';
$_lang["integrity_compare_files_new"] = 'New Files';
$_lang["integrity_compare_files_notreadable"] = 'File not readable';
$_lang["integrity_compare_dirs_excluded"] = 'Directories excluded by Image';

$_lang["search_term"] = 'Search Term';
$_lang["search_term_add"] = '(RegEx, case-insensitive)';
$_lang["summary_length"] = 'Summary Length';
$_lang["zero_to_disable"] = '(0 to disable)';
$_lang["btn_search"] = 'Search';
$_lang["btn_delete"] = 'Delete';
$_lang["btn_create"] = 'Create';
$_lang["btn_compare"] = 'Compare';
$_lang["confirm_delete"] = 'Are you sure you want to delete this Element?';
$_lang["search_db"] = 'Search DB';
$_lang["search_files"] = 'Search Files';
$_lang["changed_after"] = 'Changed after';
$_lang["changed_after_msg"] = 'Beware: The changed after-date is not reliable. File-Dates can easily be modified using PHP-function touch().';
$_lang["server_time"] = 'Search Files';
$_lang["plugins"] = 'Plugins';
$_lang["snippets"] = 'Snippets';
$_lang["templates"] = 'Templates';
$_lang["chunks"] = 'Chunks';
$_lang["content"] = 'Content';
$_lang["modules"] = 'Modules';
$_lang["searched_for"] = 'searched for';
$_lang["all_files"] = 'All Files';
$_lang["no_results"] = 'No results.';
$_lang["disclaimer"] = 'Disclaimer: We´re not liable to you for any damages like general, special, incidental or consequential damages arising out of the use or inability to use the script (including but not limited to loss of data or report being rendered inaccurate or failure of the script). There is no warranty for this script. Use at your own risk.';