<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/3/25
 * Time: 下午5:47
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\ConfigLoader;
class Table
{
    use Singleton;
    private $tables = [];

    public function getDatabase($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $this->setTables();
            if (!isset($this->tables[$tableName])) {
                throw new MysqlException('无法获取数' . $tableName . '表所在的数据库配置');
            }
        }
        return $this->tables[$tableName];
    }

    public function init()
    {
        $this->setTables();
    }

    private function setTables()
    {
        if ([] == $this->tables) {
            $tables = ConfigLoader::getInstance()->loadDistinguishBetweenFolderAndFile(Path::getTablePath());
            if (null == $tables || [] == $tables) {
                return;
            }
            foreach ($tables as $key => $table) {
                if (null == $table || [] == $table) {
                    unset($tables[$key]);
                    continue;
                }
                $tables[$key] = $this->parseTable($table);
            }
            $this->tables = $tables;
        }
        return;
    }

    private function parseTable($table)
    {
        $result = [];
        foreach ($table as $db => $tableList) {
            foreach ($tableList as $tableName) {
                $result[$tableName] = $db;
            }
        }
        return $result;
    }

}