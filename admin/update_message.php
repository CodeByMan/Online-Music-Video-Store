<?php
include("database.php");

if (isset($_GET['message_id']) && isset($_GET['message_status'])) {
    $message_id = $_GET['message_id'];
    $message_status = $_GET['message_status'];

    if ($message_status == 'seen' || $message_status == 'pending') {
        $message_id = mysqli_real_escape_string($conn, $message_id);
        
        $sql = "UPDATE messages SET message_status = '$message_status' WHERE message_id = '$message_id'";

        if (mysqli_query($conn, $sql)) {
            header("Location: message.php");
            exit;
        } else {
            echo "Error updating message status!";
        }
    } else {
        echo "Invalid status provided!";
    }
} else {
    echo "Invalid parameters!";
}
?>
