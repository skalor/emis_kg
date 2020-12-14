<?php
namespace StatisticReport\Model\Entity;

use Cake\ORM\Entity;

/**
 * Faq Entity
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property string $type
 * @property string $lang
 * @property string $filename
 * @property string $location_url
 * @property string $audit
 * @property string $category
 * @property int $modified_user_id
 * @property \Cake\I18n\Time $modified
 * @property int $created_user_id
 * @property \Cake\I18n\Time $created
 *
 * @property \StatisticReport\Model\Entity\ModifiedUser $modified_user
 * @property \StatisticReport\Model\Entity\CreatedUser $created_user
 */
class StatisticReport extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
