<?php

namespace humhub\modules\jobs\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Filter & Suche für die öffentliche Inserateliste.
 * Sortierung fix: Top-Inserate zuerst, dann nach Veröffentlichungsdatum.
 */
class JobListingSearch extends JobListing
{
    /** Gewünschtes Pensum (%) – Inserat muss es abdecken. */
    public $pensum;

    public function rules(): array
    {
        return [
            [['canton', 'setting', 'employment_type'], 'safe'],
            [['pensum'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }

    public function scenarios(): array
    {
        // Filter-Felder freigeben, Model-Pflichtregeln umgehen.
        return Model::scenarios();
    }

    /**
     * Nur aktive Inserate (veröffentlicht und nicht abgelaufen), gefiltert.
     */
    public function searchPublished(array $params): ActiveDataProvider
    {
        $query = JobListing::find()
            ->andWhere(['status' => self::STATUS_PUBLISHED])
            ->andWhere(['>=', 'published_until', new Expression('NOW()')]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['is_top' => SORT_DESC, 'published_at' => SORT_DESC],
                'attributes' => ['published_at', 'is_top'],
            ],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider; // leere/ungültige Filter -> ungefiltert
        }

        $query->andFilterWhere([
            'canton' => $this->canton,
            'setting' => $this->setting,
            'employment_type' => $this->employment_type,
        ]);

        if ($this->pensum !== null && $this->pensum !== '') {
            // Inserat deckt das gewünschte Pensum ab, oder macht keine Pensum-Angabe.
            $query->andWhere([
                'or',
                ['and', ['<=', 'pensum_min', $this->pensum], ['>=', 'pensum_max', $this->pensum]],
                ['and', ['pensum_min' => null], ['pensum_max' => null]],
            ]);
        }

        return $dataProvider;
    }
}
