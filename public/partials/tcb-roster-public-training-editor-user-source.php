<?php

function tcb_roster_public_training_editor_user_source($user_id, $form, $action) {
    return $_SESSION['foreign_user_id'];
}
