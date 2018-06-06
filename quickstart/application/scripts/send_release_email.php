<?php

if($argc != 2) {
    dddd("wrong arguments");
}

require 'includes.php';

$release_note = $argv[1];
$pattern="#.*/release-s(\d+)\.(\d+)\.md$#";
$release_file_info = array();
if(!preg_match($pattern, $release_note, $release_file_info)) {
    dddd("bad release note!");    
}

$sprint_no = $release_file_info[1];
$release_no = "$sprint_no.$release_file_info[2]";
$release_note_content = file_get_contents($release_note);
$release_note_github_url = "https://github.com/liangdev/pincommerce/blob/master/releases/release-s${release_no}.md";

$data = array(
    'site_url' => getURL(),
    'sprint_no' => $sprint_no,
    'release_no' => "RS-$release_no",
    'release_note_content' => $release_note_content,
    'release_note_github_url' => $release_note_github_url,
);

$service = new EmailService();

$service->setMethod('create_job');
$service->setParams(array(
    'to' => 'xxx@shopinterest.co',
    'from' => 'xxx@shopinterest.co',
    'type' => TECH_REPORT_RELEASE_NOTIFICATION,
    'data' => $data,
    'job_dbobj' => $job_dbobj
));

$service->call();
