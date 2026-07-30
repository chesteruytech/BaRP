<?php

define("SYSTEM_NAME", "Barangay Resident Portal");

define("SYSTEM_VERSION", "1.0");

define("SYSTEM_OWNER", "Barangay Office");


define("UPLOAD_PATH", "../../uploads/documents/");


define("ALLOWED_TYPES", [
    "pdf",
    "jpg",
    "jpeg",
    "png"
]);


define("MAX_FILE_SIZE", 5 * 1024 * 1024); // 5 MB

define("STATUS_PENDING", "Pending");
define("STATUS_APPROVED", "Approved");
define("STATUS_REJECTED", "Rejected");


define("ROLE_ADMIN", "admin");
define("ROLE_RESIDENT", "resident");

?>
