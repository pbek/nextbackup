<div class="section">
    <h2>
        <label for="backup-date-select"><?php p($l->t('Your backups'));?></label>
    </h2>
    <div class="box">
        <?php p($l->t('Select the backup you want to restore tables from.'));?>
    </div>
    <select class="chosen-select" id="backup-date-select" name="timestamp" data-placeholder="<?php p($l->t('Select backup'));?>">
        <option value="0" selected=""></option>
        <?php foreach($_['backupDateHash'] as $timestamp => $date):?>
            <option value="<?php p($timestamp);?>">
                <?php p($date);?>
            </option>
        <?php endforeach;?>
    </select>
</div>
<div class="section" id="backup-tables-block">
    <h2>
        <label for="backup-tables-select"><?php p($l->t('Tables of backup'));?></label>
    </h2>
    <div class="box">
        <?php p($l->t('Select the tables you want to restore.'));?>
    </div>
    <select id="backup-tables-select" multiple="" name="tables" data-placeholder="<?php p($l->t('Select the tables to restore'));?>"></select>
    <input type="button" id="restore-button" value="<?php p($l->t('Restore tables'));?>">
</div>
<div class="section">
    <input type="button" id="backup-button" value="<?php p($l->t('Create Backup'));?>">
</div>
