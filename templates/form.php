<?php
/**
 * @var \Avolle\UpcomingMatches\View\View $this
 * @var \Avolle\UpcomingMatches\SportConfig[] $sports
 * @var string|null $infoMessage
 */
use Cake\Chronos\Chronos;

?>
<?php if (isset($infoMessage) && strlen($infoMessage) > 0): ?>
    <div class="alert alert-info">
        <?= h($infoMessage); ?>
    </div>
<?php endif; ?>
<?php if ($this->Error->hasErrors()): ?>
    <div class="alert alert-error">
        Kunne ikke generere bilde! Noe gikk galt med innsendt informasjon.
    </div>
<?php endif; ?>
<form action="" method="GET">
    <div>
        <label for="dateFrom">Dato fra:</label>
    </div>
    <div>
        <?php $value = $this->getRequestData('dateFrom', Chronos::now()->startOfWeek()->toDateString()); ?>
        <input type="date" name="dateFrom" id="dateFrom" required="required" value="<?= $value; ?>">
        <?= $this->Error->message('dateFrom'); ?>
    </div>
    <div>
        <label for="dateTo">Dato til:</label>
    </div>
    <div>
        <?php $value = $this->getRequestData('dateTo', Chronos::now()->endOfWeek()->toDateString()); ?>
        <input type="date" name="dateTo" id="dateTo" required="required" value="<?= $value ?>">
        <?= $this->Error->message('dateTo'); ?>
    </div>
    <div>
        <label for="sport">Sport:</label>
    </div>
    <div>
        <select name="sport" id="sport" required="required">
            <?php foreach ($sports as $sport => $sportConfig): ?>
                <option
                    value="<?= $sport; ?>"
                    <?= $this->getRequestData('sport') === $sport ? 'selected="selected"' : ''; ?>
                >
                    <?= h($sportConfig->sport); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= $this->Error->message('sport'); ?>
    </div>
    <div>
        <input type="submit" value="Hente">
    </div>
</form>

<style type="text/css">
    .error {
        position: relative;
        top: -15px;
        color: indianred;
    }
    .alert {
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .alert-error {
        background: indianred;
        color: #eee;
    }
    .alert-info {
        background: darkcyan;
        color: #eee;
    }
</style>
