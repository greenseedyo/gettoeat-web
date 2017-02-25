<?php

class TablesInfo_Helper
{
    private $_pix_tabel_row;
    private $_data;

    const DEFAULT_TOTAL_WIDTH = 600;
    const DEFAULT_TOTAL_HEIGHT = 400;

    public function __construct(TablesInfoRow $tables_info)
    {
        $this->_pix_tabel_row = $tables_info;
        $this->_data = json_decode($tables_info->data);
        /* [data 格式範例]
        // {
        //     "totalHeight": "100",
        //     "totalWidth": "50",
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

    public function getTotalHeight()
    {
        return $this->_data->totalHeight ?? self::DEFAULT_TOTAL_HEIGHT;
    }

    public function getTotalWidth()
    {
        return $this->_data->totalWidth ?? self::DEFAULT_TOTAL_WIDTH;
    }

    public function save(string $data)
    {
        $this->_pix_tabel_row->update(array("data" => $data));
    }
}
