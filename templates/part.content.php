<form class="section">
    <h2>
        <label for="backup-date-select"><?php p($l->t('Backup date'));?></label>
    </h2>
    <select class="chosen-select" id="backup-date-select" name="timestamp" data-placeholder="<?php p($l->t('Select the backup date'));?>">
        <option value="0" selected=""></option>
        <?php foreach($_['backupDateHash'] as $timestamp => $date):?>
            <option value="<?php p($timestamp);?>">
                <?php p($date);?>
            </option>
        <?php endforeach;?>
    </select>
    <div id="backup-tables-block">
        <h2>
            <label for="backup-tables-select"><?php p($l->t('Backup tables'));?></label>
        </h2>
        <select id="backup-tables-select" multiple="" name="tables" data-placeholder="<?php p($l->t('Select the tables to restore'));?>"></select>
        <input type="button" id="restore-button" value="<?php p($l->t('Restore tables'));?>">
    </div>
    <p>
        <input type="button" id="backup-button" value="<?php p($l->t('Create Backup'));?>">
    </p>
</form>
