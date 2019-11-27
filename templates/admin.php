<?php
    script('nextbackup', ['chosen.jquery.min', 'script']);
    style('nextbackup', ['chosen.min', 'style']);
?>

<div id="nextbackup">
    <div class="section">
        <h2><?php p($l->t('NextBackup'));?></h2>
        <h3>
            <label for="nextbackup-backup-date-select"><?php p($l->t('Your backups'));?></label>
        </h3>
        <div class="box">
            <?php p($l->t('Select the backup you want to restore tables from.'));?>
        </div>
        <select class="chosen-select" id="nextbackup-backup-date-select" name="timestamp" data-placeholder="<?php p($l->t('Select backup'));?>">
            <option value="0" selected=""></option>
            <?php foreach($_['backupDateHash'] as $timestamp => $date):?>
                <option value="<?php p($timestamp);?>">
                    <?php p($date);?>
                </option>
            <?php endforeach;?>
        </select>
        <div id="nextbackup-backup-tables-block">
            <h3>
                <label for="nextbackup-backup-tables-select"><?php p($l->t('Tables of backup'));?></label>
            </h3>
            <div class="box">
                <?php p($l->t('Select the tables you want to restore.'));?>
            </div>
            <select id="nextbackup-backup-tables-select" multiple="" name="tables" data-placeholder="<?php p($l->t('Select the tables to restore'));?>"></select>
            <button class="icon-button" id="nextbackup-select-all-tables-button" title="<?php p($l->t('Select all tables'));?>">
                <span class="icon-checkmark"></span>
            </button>
            <button class="icon-button" id="nextbackup-deselect-all-tables-button" title="<?php p($l->t('Deselect all tables'));?>">
                <span class="icon-close"></span>
            </button>
            <input type="button" id="nextbackup-restore-button" title="<?php p($l->t('Restore all selected tables'));?>" value="<?php p($l->t('Restore tables'));?>">
        </div>
        <input type="button" id="nextbackup-backup-button" title="<?php p($l->t('Create a new backup of all tables'));?>" value="<?php p($l->t('Create Backup'));?>">
        <div id="nextbackup-backup-message" class="message-box">
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
        <div id="nextbackup-cover"></div>
        <div class="box">
            <em>
                <?php p($l->t("Don't forget to setup a cronjob to get periodic backups."));?><br />
                <!--
                <?php p($l->t("If you want an easy Webcron solution you might want to try"));?>:
                <a href="https://www.easycron.com?ref=70375" target="_blank">EasyCron</a>
                -->
            </em>
        </div>
    </div>
</div>
