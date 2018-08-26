<?php
function xmldb_mymodule_upgrade($oldversion) {

    $result = TRUE;
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2013012900) {

        // Define field quiz_id to be added to qtype_keystrokerecorder.
        $table = new xmldb_table('qtype_keystrokerecorder');
        $field = new xmldb_field('quiz_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

        // Conditionally launch add field quiz_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Keystrokerecorder savepoint reached.
        upgrade_plugin_savepoint(true, 2013012900, 'qtype', 'keystrokerecorder');
    }
    return $result;
}
?>