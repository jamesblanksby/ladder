<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// FILE /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* --------------------------------------------------------------------- UPLOAD --- */
function file_upload($source, $target) {
    return rename($source, $target);
}
