<?php



asbstrac class MetaUpgraderTask
{

    protected function $params = null;

    protected function $moduleObject = null;

    public function __construct($moduleObject, $params = null)
    {
        $this->params = $params;
        $this->moduleObject = $moduleObject;
    }

    public function run()
    {
        $this->starter();
        $this->upgrader();
        $this->ender();
    }

    abstract function upgrader();


    public function starter()
    {

    }

    public function ender()
    {
        //commit and push!
    }

}
