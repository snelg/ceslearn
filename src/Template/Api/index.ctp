<?php
if (!empty($output)) {
    if ($output instanceof Cake\Orm\Query || $output instanceof Cake\ORM\ResultSet) {
        foreach ($output as $row) {
            pr($row);
        }
    } else {
        pr($output);
    }
}
