<?php
namespace Statistic\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmployeesReport Entity
 *
 * @property int $id
 * @property string $region
 * @property string $name
 * @property string $org_struct
 * @property int $area_level_id
 * @property string $code
 * @property int $staff_male_count
 * @property int $staff_female_count
 * @property int $staff_total_count
 * @property \Cake\I18n\Time $create_at
 * @property int $student_male_count
 * @property int $student_female_count
 * @property int $student_total_count
 * @property int $all_total_count
 *
 * @property \Employees\Model\Entity\AreaLevel $area_level
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
