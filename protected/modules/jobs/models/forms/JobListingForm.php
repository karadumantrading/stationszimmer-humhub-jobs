<?php

namespace humhub\modules\jobs\models\forms;

use humhub\modules\jobs\models\JobListing;

/**
 * Erfassungs-/Edit-Formular für Inserate. Erbt vom Model, erzwingt aber das
 * Arbeitgeber-Szenario: nur fachliche Felder sind befüllbar, niemals
 * status/tier/stripe/published_* (die setzt ausschliesslich der Server).
 */
class JobListingForm extends JobListing
{
    public function init(): void
    {
        parent::init();
        $this->scenario = self::SCENARIO_EMPLOYER;
    }
}
