<?php

namespace backtend\phpfifth;


/**
 * 载入初始数组，生成嵌套格式的树形数组
 *
 * Class TreeLib
 * @package backtend\phplib
 *
 * $data = [
 * ["id" => 1, "pid" => 0, 'title' => 'Extend Value'],
 * ["id" => 2, "pid" => 0, 'title' => 'Extend Value'],
 * ["id" => 3, "pid" => 1, 'title' => 'Extend Value'],
 * ["id" => 4, "pid" => 3, 'title' => 'Extend Value'],
 * ["id" => 5, "pid" => 2, 'title' => 'Extend Value'],
 * ["id" => 100, "pid" => 0, 'title' => 'Extend Value'],
 * ["id" => 1000, "pid" => 100, 'title' => 'Extend Value'],
 * ["id" => 10000, "pid" => 1000, 'title' => 'Extend Value'],
 * ["id" => 100000, "pid" => 10000, 'title' => 'Extend Value'],
 * ["id" => 1000000, "pid" => 100000, 'title' => 'Extend Value'],
 * ["id" => 10000000, "pid" => 1000000, 'title' => 'Extend Value'],
 * ["id" => 100000000, "pid" => 10000000, 'title' => 'Extend Value'],
 * ["id" => 1000000000, "pid" => 100000000, 'title' => 'Extend Value'],
 * ];
 * halts(TreeLib::make($data, null, ['parent_field' => 'pid']));
 * halts(TreeLib::make($data, 100, ['parent_field' => 'pid']));
 *
 */
class TreeLib
{
    static $_pkField;//主键字段名
    static $_parentField;//上级id字段名
    static $_childrenField;//用来存储子分类的数组key名

    public static function make(array $data, $root = null, array $options = array())
    {
        if (empty($data)) {
            return array();//throw new \Exception('请先调用load入参data');
        }
        $root = $root === null ? 0 : $root;
        self::$_pkField = isset($options['pk_field']) ? $options['pk_field'] : 'id';
        self::$_parentField = isset($options['parent_field']) ? $options['parent_field'] : 'parent_id';
        self::$_childrenField = isset($options['children_field']) ? $options['children_field'] : 'children';

        $tree = array();//最终数组
        $refer = array();//存储主键与数组单元的引用关系
        //遍历
        foreach ($data as $k => $v) {
            if (!isset($v[self::$_pkField]) or !isset($v[self::$_parentField]) or isset($v[self::$_childrenField])) {
                unset($data[$k]);
                continue;
            }
            $refer[$v[self::$_pkField]] =& $data[$k];//为每个数组成员建立引用关系
        }

        //遍历2
        foreach ($data as $k => $v) {
            if ($v[self::$_parentField] == $root) {//根分类直接添加引用到tree中
                $tree[] =& $data[$k];
            } else {
                if (isset($refer[$v[self::$_parentField]])) {
                    $parent =& $refer[$v[self::$_parentField]];//获取父分类的引用
                    $parent[self::$_childrenField][] =& $data[$k];//在父分类的children中再添加一个引用成员
                }
            }
        }
        return $tree;
    }
}
