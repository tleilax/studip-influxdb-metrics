<h2>InfluxDB-Einstellungen</h2>

<? if ($active) : ?>

    <dl>
        <dt>InfluxDB-Host</dt>   <dd><?= htmlReady($settings['host']) ?></dd>
        <dt>InfluxDB-Port</dt> <dd><?= htmlReady($settings['port']) ?></dd>
    </dl>

    <form action="<?= PluginEngine::getLink($plugin, array(), 'deactivate') ?>" method="post">
        <?= \Studip\Button::createAccept(_("Deaktivieren")) ?>
    </form>

<? else : ?>

    <form action="<?= PluginEngine::getLink($plugin, array(), 'settings') ?>" method="post">

        <fieldset>

            <legend>Wo befindet sich Ihr InfluxDB?</legend>

            <label>
                <?= _('Host:') ?>
                <input required type="text" name="settings[host]" value="<?= htmlReady($settings['host']) ?>">
            </label>

            <label>
                <?= _('Port:') ?>
                <input required type="text" name="settings[port]" value="<?= htmlReady($settings['port']) ?>">
            </label>

        </fieldset>

        <div class="button-group">
            <?= \Studip\Button::createAccept(_("Übernehmen und aktivieren")) ?>
        </div>
    </form>

<? endif ?>
