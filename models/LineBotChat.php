<?php

class LineBotChatRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
        $this->updated_at = time();
    }

    public function preUpdate($columns = array())
    {
        $this->updated_at = time();
    }

    public function getBotClient()
    {
        $config = json_decode(GAV::find('line-bot-config')->value);
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($config->access_token);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $config->channel_secret]);
        return $bot;
    }

    public function pushMessage($message)
    {
        $bot = $this->getBotClient();
        $builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
        $result = $bot->pushMessage($this->source_id, $builder);
        return $result;
    }

    public function replyMessage($reply_token, $message)
    {
        $bot = $this->getBotClient();
        $builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
        $result = $bot->replyMessage($reply_token, $builder);
        return $result;
    }
}

class LineBotChat extends Pix_Table
{
    public $_rowClass = 'LineBotChatRow';

    public function init()
    {
        $this->_name = 'line_bot_chat';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['source_type'] = array('type' => 'varchar', 'size' => 10);
        $this->_columns['source_id'] = array('type' => 'varchar', 'size' => 10);
        $this->_columns['joined_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['left_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('source', array('source_type', 'source_id'));
        $this->addIndex('joined_at', array('joined_at'));
        $this->addIndex('left_at', array('left_at'));
        $this->addIndex('created_at', array('created_at'));
        $this->addIndex('updated_at', array('updated_at'));

        $this->_relations['store'] = array('rel' => 'has_one', 'type' => 'Store', 'foreign_key' => 'store_id');
    }

    public function getBySource($source_type, $source_id)
    {
        return self::search(array('source_type' => $source_type, 'source_id' => $source_id))->first();
    }
}

