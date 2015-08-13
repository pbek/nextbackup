<?php
    script('ownbackup', ['chosen.jquery.min', 'script']);
    style('ownbackup', ['chosen.min', 'style']);
?>

<div id="ownbackup">
    <div class="section">
        <h2><?php p($l->t('OwnBackup'));?></h2>
        <h3>
            <label for="ownbackup-backup-date-select"><?php p($l->t('Your backups'));?></label>
        </h3>
        <div class="box">
            <?php p($l->t('Select the backup you want to restore tables from.'));?>
        </div>
        <select class="chosen-select" id="ownbackup-backup-date-select" name="timestamp" data-placeholder="<?php p($l->t('Select backup'));?>">
            <option value="0" selected=""></option>
            <?php foreach($_['backupDateHash'] as $timestamp => $date):?>
                <option value="<?php p($timestamp);?>">
                    <?php p($date);?>
                </option>
            <?php endforeach;?>
        </select>
        <div id="ownbackup-backup-tables-block">
            <h3>
                <label for="ownbackup-backup-tables-select"><?php p($l->t('Tables of backup'));?></label>
            </h3>
            <div class="box">
                <?php p($l->t('Select the tables you want to restore.'));?>
            </div>
            <select id="ownbackup-backup-tables-select" multiple="" name="tables" data-placeholder="<?php p($l->t('Select the tables to restore'));?>"></select>
            <button class="icon-button" id="ownbackup-select-all-tables-button" title="<?php p($l->t('Select all tables'));?>">
                <span class="icon-checkmark"></span>
            </button>
            <button class="icon-button" id="ownbackup-deselect-all-tables-button" title="<?php p($l->t('Deselect all tables'));?>">
                <span class="icon-close"></span>
            </button>
            <input type="button" id="ownbackup-restore-button" title="<?php p($l->t('Restore all selected tables'));?>" value="<?php p($l->t('Restore tables'));?>">
        </div>
        <input type="button" id="ownbackup-backup-button" title="<?php p($l->t('Create a new backup of all tables'));?>" value="<?php p($l->t('Create Backup'));?>">
        <div id="ownbackup-backup-message" class="message-box">
            <h3>
                <?php p($l->t('Backup in process, please wait!'));?>
            </h3>
            <div class="icon-loading wait-spinner">&nbsp;</div>
        </div>
        <div id="restore-message" class="message-box">
            <h3>
                <?php p($l->t('Tables are being restored, please wait!'));?>
            </h3>
            <div class="icon-loading wait-spinner">&nbsp;</div>
        </div>
        <div id="ownbackup-cover"></div>
    </div>
</div>
