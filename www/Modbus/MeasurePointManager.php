<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: MeasurePointManager.php
 * $Id: measurePointIndex.php v 1.0 2016-07-05 20:23:18 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2019-01-08 17:47:37 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus;

use app\utility\Singleton;

// 系统测点定义
define('DT_NOT_EXIST',                               'NotExist');
define('DT_SYS_UPDATE_TIME',                         'DT_SYS_UPDATE_TIME');

//测点表
//系统测点(0x8000)
define('DT_SYS_PACK_NUM',                            'DT_SYS_PACK_NUM');

//补充测点
//充电状态　根据电流的正负来判断是均充还是放电 均充20412，浮充20411，放电20406，开路20414
define('DT_SYS_CHARGE_STATUS',                       'DT_SYS_CHARGE_STATUS');
//总电压　所有的单体电压求和
define('DT_SYS_TOTAL_VOLT',                          'DT_SYS_TOTAL_VOLT');
//总电流　　　所有的组电流求和
define('DT_SYS_TOTAL_CURRENT',                       'DT_SYS_TOTAL_CURRENT');
//环境温度　　环境温度１和环境温度２求平均
define('DT_SYS_ENVI_TEMP',                           'DT_SYS_ENVI_TEMP');
//总soc　　　暂无计算
define('DT_SYS_TOTAL_SOC',                           'DT_SYS_TOTAL_SOC');
//总soh　　　暂无计算
define('DT_SYS_TOTAL_SOH',                           'DT_SYS_TOTAL_SOH');
//充放电状态
define('DT_BT_CHARGE_STATUS',                        'DT_BT_CHARGE_STATUS');
//备电时间   暂无计算
define('DT_SYS_BACKUP_TIME',                         'DT_SYS_BACKUP_TIME');
//电池组平均环境温度
define('DT_BT_ENVI_TEMP',                            'DT_BT_ENVI_TEMP');
//电池组总电压
define('DT_BT_TOTAL_VOLT',                           'DT_BT_TOTAL_VOLT');
//电池组剩余安时
define('DT_BT_REAL_AH',                              'DT_BT_REAL_AH');
//电池组基准安时，充放电切换时，记录
define('DT_BT_BASE_AH',                              'DT_BT_BASE_AH');
//电池组的soc和soh
define('DT_BT_PACK_SOC',                             'DT_BT_PACK_SOC');
define('DT_BT_PACK_SOH',                             'DT_BT_PACK_SOH');
//实际备电时长DT_SYS_BACKTIME,额定容量*%SOH*倍率系数/当前负载
define('DT_SYS_BACKTIME',                            'DT_SYS_BACKTIME');
//剩余备电时长
define('DT_SYS_SURPLYS_BACKUP_TIME',                 'DT_SYS_SURPLYS_BACKUP_TIME');
//电池组充电大于0.02C持续时间
define('DT_BT_CURR_TIME_LONG',                       'DT_BT_CURR_TIME_LONG');
define('DT_BT_CURR_BASE_TIME',                       'DT_BT_CURR_BASE_TIME');

define('DT_BT_CELL_AVE_R',                           'DT_BT_CELL_AVE_R');
define('DT_BT_CELL_AVE_VOLT',                        'DT_BT_CELL_AVE_VOLT');

//电池组测点(0x81xx)
define('DT_BT_CELL_NUM',                             'DT_BT_CELL_NUM');
define('DT_BT_PACK_CURR',                            'DT_BT_PACK_CURR');
define('DT_BT_ENV_TEMP_1',                           'DT_BT_ENV_TEMP_1');
define('DT_BT_ENV_TEMP_2',                           'DT_BT_ENV_TEMP_2');
define('DT_BT_TC_STATUS',                            'DT_BT_TC_STATUS');
define('DT_BT_AH',                                   'DT_BT_AH');
define('DT_BT_CURR_FACTOR',                          'DT_BT_CURR_FACTOR');
define('DT_BT_TC_CONNECTION',                        'DT_BT_TC_CONNECTION');
define('DT_BT_CELL_UNIT_VOLT',                       'DT_BT_CELL_UNIT_VOLT');
define('DT_BT_CELL_UNIT_TEMP',                       'DT_BT_CELL_UNIT_TEMP');
define('DT_BT_CELL_UNIT_R',                          'DT_BT_CELL_UNIT_R');
define('DT_BT_CELL_UNIT_SOC',                        'DT_BT_CELL_UNIT_SOC');
define('DT_BT_CELL_UNIT_SOH',                        'DT_BT_CELL_UNIT_SOH');
define('DT_BT_CELL_UNIT_TEMP_ALARM',                 'DT_BT_CELL_UNIT_TEMP_ALARM');
define('DT_BT_CELL_UNIT_R_ALARM',                    'DT_BT_CELL_UNIT_R_ALARM');
define('DT_BT_CELL_UNIT_TA_STATUS',                  'DT_BT_CELL_UNIT_TA_STATUS');
define('DT_BT_CELL_UNIT_TA_CONNECTION',              'DT_BT_CELL_UNIT_TA_CONNECTION');

// 模块代码定义
define('MODULE_SYS_SERVER',        0x00000000); // 系统模块-服务端定义
define('MODULE_SYS',               0x80000000); // 系统模块
define('MODULE_BT',                0x81000000); // 电池组模块
// 模块类型掩码
define('MODULE_TYPE_MASK',  0xFF000000);

// 模块内索引编号掩码
define('MODULE_INDEX_MASK', 0x00FF0000);

class MeasurePointManager extends Singleton {
    protected static $_instance = null;
    protected function init() {
        $this->tableInit();
    }

    const IDX_MP        = 0;
    const IDX_UNIT      = 1;
    const IDX_PRECISION = 2;
    const IDX_VALUETYPE = 3;
    const IDX_VALUELEN  = 4;
    const IDX_NAME      = 5;

    // 支持的数值类型
    const Float     = 'Float';
    const Uint32    = 'Uint32';
    const Uint16    = 'Uint16';
    const Uint8     = 'Uint8';
    const Int8      = 'Int8';
    const String    = 'String';
    const IP        = 'IP';

    // 模块名称
    private $moduleNames = array(
        MODULE_SYS_SERVER            => '站点',
        MODULE_SYS                   => '站点',
        MODULE_BT                    => '电池组',
    );

    private $dataTypeMap;
    /**
     * 测点表
     */
    public $table = array(
        /* 测点编号，单位，小数点精度，数值类型，测点名称 */
        // 系统测点（0x00）
        DT_NOT_EXIST                           => array(0x00000000, '',    0,  self::Float,    4,  '测点不存在'),
        DT_SYS_UPDATE_TIME                     => array(0x00000001, '',    0,  self::String,   18, '最后更新时间'),

        //测点表
        //系统测点(0x8000)
        DT_SYS_PACK_NUM                        => array(0x80001001, '',    0,  self::Uint8,    1,  '电池组数量'),
        //系统计算增加测点  0x00001XXXX  0x00002XXX
        DT_SYS_CHARGE_STATUS                   => array(0x00001001, '',    0,  self::Uint8,    5,  '充电状态'),
        DT_SYS_TOTAL_VOLT                      => array(0x00001002, 'V',   3,  self::Float,    4,  '总电压'),
        DT_SYS_TOTAL_CURRENT                   => array(0x00001003, 'A',   1,  self::Float,    4,  '总电流'),
        DT_SYS_ENVI_TEMP                       => array(0x00001004, '°C',  1,  self::Float,    4,  '环境温度'),
        DT_SYS_TOTAL_SOC                       => array(0x00001005, '%',   2,  self::Float,    2,  '总soc'),
        DT_SYS_TOTAL_SOH                       => array(0x00001006, '%',   2,  self::Float,    2,  '总soh'),
        DT_SYS_BACKUP_TIME                     => array(0x00001007, 'h',   2,  self::Uint8,    4,  '备电时间'),

        DT_BT_ENVI_TEMP                        => array(0x00001008, '°C',  1,  self::Float,    4,  '电池组平均环境温度'),
        DT_BT_TOTAL_VOLT                       => array(0x00001009, 'V',   3,  self::Float,    4,  '电池组总电压'),
        DT_BT_REAL_AH                          => array(0x0000100A, 'AH',  1,  self::Float,    4,  '剩余安时'),
        DT_BT_BASE_AH                          => array(0x0000100B, 'AH',  1,  self::Float,    4,  '基准安时'),


        DT_BT_PACK_SOC                         => array(0x0000100C, '%',   1,  self::Float,    4,  '组SOC'),
        DT_BT_PACK_SOH                         => array(0x0000100D, '%',   1,  self::Float,    4,  '组SOH'),
        DT_BT_CHARGE_STATUS                    => array(0x0000100E, '',    0,  self::Uint8,    5,  '充电状态'),
        DT_SYS_BACKTIME                        => array(0x00001010, 'H',   1,  self::Float,    4,  '备电时长'),
        DT_SYS_SURPLYS_BACKUP_TIME             => array(0x00001011, 'H',   1,  self::Float,    4,  '剩余备电时长'),

        DT_BT_CURR_TIME_LONG                   => array(0x00001012, 'h',   2,  self::Uint8,    4,  '充电>0.02C持续时间'),
        DT_BT_CURR_BASE_TIME                   => array(0x00001013, '',    0,  self::String,   18, '充电>0.02C开始时间'),

        DT_BT_CELL_AVE_R                       => array(0x00001014, 'uΩ',  0,  self::Uint32,   4,  '电池组内电池平均内阻'),
        DT_BT_CELL_AVE_VOLT                    => array(0x00001015, 'V',   3,  self::Float,    4,  '电池组内电池平均电压'),
        //电池组测点(0x81xx)
        DT_BT_CELL_NUM                         => array(0x81001000, '',    0,  self::Uint8,    1,  '组电池数量'),
        DT_BT_PACK_CURR                        => array(0x81001001, 'A',   1,  self::Float,    4,  '充放电电流'),
        DT_BT_ENV_TEMP_1                       => array(0x81001002, '°C',  1,  self::Float,    4,  '环境温度1'),
        DT_BT_ENV_TEMP_2                       => array(0x81001003, '°C',  1,  self::Float,    4,  '环境温度2'),
        DT_BT_TC_STATUS                        => array(0x81001004, '',    0,  self::Uint8,    1,  'TC设备状态'),
        DT_BT_AH                               => array(0x81001005, 'AH',  1,  self::Float,    4,  '安时数'),
        DT_BT_CURR_FACTOR                      => array(0x81001006, '',    1,  self::Float,    4,  '充放电电流倍率'),
        DT_BT_TC_CONNECTION                    => array(0x81001007, '',    0,  self::Uint8,    1,  'TC设备通讯状态'),
    );

    public function tableInit() {
        $table    = $this->table;
        $newTable = array();
        for($i = 0; $i < 256; $i++) {
            //计算测点
            $tpVolt = 0x81001100 + $i;
            $newTable[DT_BT_CELL_UNIT_VOLT.'_'.($i+1)] = array($tpVolt, 'V',   3,  self::Float,    4,  '电池电压'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_VOLT.'_'.($i+1), DT_BT_CELL_UNIT_VOLT.'_'.($i+1));
            $tpTemp = 0x81001200 + $i;
            $newTable[DT_BT_CELL_UNIT_TEMP.'_'.($i+1)] = array($tpTemp, '°C',  1,  self::Float,    4,  '电池温度'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_TEMP.'_'.($i+1), DT_BT_CELL_UNIT_TEMP.'_'.($i+1));
            $tpR    = 0x81001300 + $i;
            $newTable[DT_BT_CELL_UNIT_R.'_'.($i+1)] = array($tpR, 'uΩ',  0,  self::Uint32,   4,  '电池内阻'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_R.'_'.($i+1), DT_BT_CELL_UNIT_R.'_'.($i+1));
            $tpSoc = 0x81001400 + $i;
            $newTable[DT_BT_CELL_UNIT_SOC.'_'.($i+1)] = array($tpSoc, '%',   2,  self::Float,    2,  '电池SOC'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_SOC.'_'.($i+1), DT_BT_CELL_UNIT_SOC.'_'.($i+1));
            $tpSoh = 0x81001500 + $i;
            $newTable[DT_BT_CELL_UNIT_SOH.'_'.($i+1)] = array($tpSoh, '%',   2,  self::Float,    2,  '电池SOH'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_SOH.'_'.($i+1), DT_BT_CELL_UNIT_SOH.'_'.($i+1));
            $tpTempAlarm = 0x8100F100 + $i;
            $newTable[DT_BT_CELL_UNIT_TEMP_ALARM.'_'.($i+1)] = array($tpTempAlarm, '',    0,  self::Uint8,    1,  '电池温度告警'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_TEMP_ALARM.'_'.($i+1), DT_BT_CELL_UNIT_TEMP_ALARM.'_'.($i+1));
            $tpRAlarm = 0x8100F200 + $i;
            $newTable[DT_BT_CELL_UNIT_R_ALARM.'_'.($i+1)] = array($tpRAlarm, '',    0,  self::Uint8,    1,  '电池内阻告警'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_R_ALARM.'_'.($i+1), DT_BT_CELL_UNIT_R_ALARM.'_'.($i+1));
            $tpTaStatus = 0x8100F300 + $i;
            $newTable[DT_BT_CELL_UNIT_TA_STATUS.'_'.($i+1)] = array($tpTaStatus, '',    0,  self::Uint8,    1,  'TA设备状态'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_TA_STATUS.'_'.($i+1), DT_BT_CELL_UNIT_TA_STATUS.'_'.($i+1));
            $tpTaConnection = 0x8100F400 + $i;
            $newTable[DT_BT_CELL_UNIT_TA_CONNECTION.'_'.($i+1)] = array($tpTaConnection, '',    0,  self::Uint8,    1,  'TA设备通讯状态'.'_'.($i+1));
            define(DT_BT_CELL_UNIT_TA_CONNECTION.'_'.($i+1), DT_BT_CELL_UNIT_TA_CONNECTION.'_'.($i+1));
        }
        $this->table = array_merge($table, $newTable);
        foreach ($this->table as $k => $v) {
            $this->dataTypeMap[$v[0]] = $k;
        }
    }

    public function getSomePointDataBasedOnText($value){
        $value = $value >> 24;
        $returnInfo = [];
        foreach ($this->table as $k => $v) {
            $curType = $v[0] >> 24;
            if ($curType == $value){
                $returnInfo[] = ['key' => $k, 'name' => $v[self::IDX_NAME]];
            }
        }
        return $returnInfo;
    }

    public function getMeasurePointCode($index){
        return $this->getMeasurePointProperty($index, self::IDX_MP);
    }

    public function getMeasurePointName($index){
        return $this->getMeasurePointProperty($index, self::IDX_NAME);
    }

    public function getMeasurePointProperty($measurePointIndex, $index){
        if (isset($this->table[$measurePointIndex])){
            return $this->table[$measurePointIndex][$index];
        }else{
            return false;
        }
    }

    public function getMeasureUnit($index){
        return $this->getMeasurePointProperty($index, self::IDX_UNIT);
    }

    public function getMeasurePre($index){
        return $this->getMeasurePointProperty($index, self::IDX_PRECISION);
    }

    public function getMeasureType($index){
        return $this->getMeasurePointProperty($index, self::IDX_VALUETYPE);
    }

    public function getMpNameAndUnitByMp($mpCode){
        foreach ($this->table as $value){
            if($mpCode == $value[0]){
                return ['unit' => $value[self::IDX_UNIT], 'name' => $value[self::IDX_NAME]];
            }
        }
        return false;
    }

    public function mp($type, $idx = 0) {
        if (isset($this->table[$type]) == false) {
            return $this->table[DT_NOT_EXIST][self::IDX_MP];
        }
        $tp = $this->table[$type][self::IDX_MP];
        $tp = $tp | (($idx << 16) & MODULE_INDEX_MASK);
        return $tp;
    }
    public function getRemark($type){
        $remark = @ $this->table[$type][5] ? $this->table[$type][5] : '';
        return $remark;
    }
    public function moduleIndex($tp) {
        return ($tp & MODULE_INDEX_MASK) >> 16;
    }

    public function dataType($tp) {
        $tp = $tp & (~MODULE_INDEX_MASK);
        if (isset($this->dataTypeMap[$tp]) == false) {
            return DT_NOT_EXIST;
        }
        return $this->dataTypeMap[$tp];
    }

    public function info($tp) {
        $dt = $this->dataType($tp);
        return $this->table[$dt];
    }

    public function unit($tp) {
        $dt = $this->dataType($tp);
        return $this->table[$dt][self::IDX_UNIT];
    }

    public function precision($tp) {
        $dt = $this->dataType($tp);
        return $this->table[$dt][self::IDX_PRECISION];
    }

    public function valueType($tp) {
        $dt = $this->dataType($tp);
        return $this->table[$dt][IDX_VALUETYPE];
    }

    public function valueLength($tp) {
        $dt = $this->dataType($tp);
        return $this->table[$dt][IDX_VALUELEN];
    }

    public function name($tp) {
        $dt = $this->dataType($tp);
        $module = $this->module($tp);
        $moduleName = $this->moduleName($tp);
        $mpName = translate('app', $this->table[$dt][self::IDX_NAME]);
        if ($module == MODULE_BT) {
            $modelIndex = $this->moduleIndex($tp);
            $name = $moduleName . ($modelIndex+1) . '-' . $mpName;
        }
        else {
            $name = $moduleName . '-' . $mpName;
        }
        return $name;
    }

    public function module($tp) {
        return $tp & MODULE_TYPE_MASK;
    }

    public function moduleName($tp) {
        $module = $this->module($tp);
        return translate('app', arrayValue($this->moduleNames, $module, 'Unknown'));
    }
}
