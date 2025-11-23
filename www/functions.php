<?php

function page_title($title = "Test funkčnosti") {
    echo htmlspecialchars($title);
}

function stylesheet($stylesheet = "style.css") {
    echo htmlspecialchars("css/" . $stylesheet);
}
?>