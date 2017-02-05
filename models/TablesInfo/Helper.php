<?php

class TablesInfo_Helper
{
    private $_pix_tabel_row;
    private $_data;

    public function __construct(TablesInfoRow $tables_info)
    {
        $this->_pix_tabel_row = $tables_info;
        $this->_data = json_decode($tables_info->data);
        /* [data 格式範例]
        // {
        //     "maxHeight": "100",
        //     "maxWidth": "50",
        //     "tables": [
        //         0: {
        //             "name": "吧1",
        //             "x": "0",
        //             "y": "0",
        //             "height": "10",
        //             "width": "10"
        //         },
        //         1: {
        //             "name": "吧1",
        //             "x": "0",
        //             "y": "0",
        //             "height": "10",
        //             "width": "10",
        //             "isTakeout": "1"
        //         }
        //     ]
        // }
        */
    }

    public function getTables(): array
    {
        $tables = $this->_data->tables ?? array();
        return $tables;
    }

    public function addTable($name)
    {
        $this->_data->tables[] = array("name" => $name, "width" => 80, "height" => 40);
    }

    public function renameTable($key, $name)
    {
        $this->_data->tables[$key]->name = $name;
    }

    public function moveTable($key, $x, $y)
    {
        $this->_data->tables[$key]->x = $x;
        $this->_data->tables[$key]->y = $y;
    }

    public function resizeTable($key, $height, $width)
    {
        $this->_data->tables[$key]->height = $height;
        $this->_data->tables[$key]->width = $width;
    }

    public function deleteTable($key)
    {
        unset($this->_data->tables[$key]);
    }

    public function save()
    {
        $data = json_encode($this->_data);
        $this->_pix_tabel_row->update(array("data" => $data));
    }
}
