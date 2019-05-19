<?php
require_once './Database.php';
require_once './team-member.php';

$members = TeamMember::findByTeam($_POST['id']);

echo json_encode($members);