<?php
namespace Test3;

error_reporting(E_ALL);

class newBase
{
    static private $count = 0;
    static private $arSetName = [];
    /**
     * @param string $name
     */
    function __construct(int $name = 0)
    {
        if (empty($name)) {
            while (array_search(self::$count, self::$arSetName) != false) {
                ++self::$count;
            }
            $name = self::$count;
        }
        $this->name = $name;
        self::$arSetName[] = $this->name;
    }
    protected $name;//'private $name;' must be inhereted because it is used in an inherited class
    /**
     * @return string
     */
    public function getName(): string
    {
        return '*' . $this->name  . '*';
    }
    protected $value;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    /**
     * @return string
     */
    public function getSize()
    {
        $size = strlen(serialize($this->value));
        return $size;//what 'strlen($size) + ' is it?
    }
    public function __sleep()
    {
        return ['name', 'value'];//there is more properties
    }
    /**
     * @return string
     */
    public function getSave(): string
    {
        $value = serialize($this->value);//it must have a $this->value
        return $this->name . ':' . sizeof($this->value) . ':' . $value;//sizeof counts an amount of elements of object
    }
    /**
     * @return newBase
     */
    static public function load(string $value): newBase
    {
        $arValue = explode(':', $value);

        //it must return a newBase instance and it returned a result of performing of a setValue method
        $ob = new newBase($arValue[0]);
        $ob->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1), $arValue[1]));
        return $ob;
    }
}
class newView extends newBase
{
    private $type = null;
    private $size = 0;
    private $property = null;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->setType();
        $this->setSize();
    }
    public function setProperty($value)
    {
        $this->property = $value;
        return $this;
    }
    private function setType()
    {
        $this->type = gettype($this->value);
    }
    private function setSize()
    {
        if (is_subclass_of($this->value, "Test3\\newBase")) {//we need to correct a string "Test3\newView" to the 'Test3\\newBase'
            $this->size = parent::getSize() + 1 + strlen($this->property);
        } elseif ($this->type == "Test3\\newBase") {//is this class is Test3\\newBase?
            $this->size = parent::getSize();
        } else {
            $this->size = strlen($this->value);
        }
    }
    /**
     * @return string
     */
    public function __sleep()
    {
        return ['property'];
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            throw new \Exception('The object doesn\'t have name');//it must be a fully qualified name \Exception
        }
        return '"' . $this->name  . '": ';
    }
    /**
     * @return string
     */
    public function getType(): string
    {
        return ' type ' . $this->type  . ';';
    }
    /**
     * @return string
     */
    public function getSize(): string
    {
        return ' size ' . $this->size . ';';
    }
    public function getInfo()
    {
        try {
            echo $this->getName()
                . $this->getType()
                . $this->getSize()
                . "\r\n";
        } catch (\Exception $exc) {//it must be a fully qualified name \Exception
            echo 'Error: ' . $exc->getMessage();
            exit();
        }
    }
    /**
     * @return string
     */
    public function getSave(): string
    {
        //this is not need
        //if ($this->type == 'test') {
        //    $this->value = $this->value->getSave();
        //}
        return parent::getSave() . ':' . serialize($this->property);//to need to add :
    }
    /**
     * @return newView
     */
    static public function load(string $value): newBase
    {
        $arValue = explode(':', $value);
        //it must return a newView instance and it returned a result of performing of a setValue method
        $ob = new newView($arValue[0]);

        $ob->setValue(unserialize(substr($value, strlen($arValue[0]) + 1 
            + strlen($arValue[1]) + 1), [$arValue[4]]));//a second paremitor must be an array and has a type of newBase

        $ob->setProperty(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1 + strlen($arValue[2]) + 1
            + strlen($arValue[3]) + 1 + strlen($arValue[4]) + 1
            + strlen($arValue[5]) + 1 + strlen($arValue[6]) + 1
            + strlen($arValue[7]) + 1 + strlen($arValue[8]) + 1
            + strlen($arValue[9]) + 1 + strlen($arValue[10]) + 1
            + strlen($arValue[11]) + 1 + strlen($arValue[12]) + 1
            + strlen($arValue[13]) + 1)));

        return $ob;
    }
}
function gettype($value): string
{
    if (is_object($value)) {
        $type = get_class($value);
        do {
            if (strpos($type, "Test3\\newBase") !== false) {//we need to correct a string "Test3\newBase" 
                return 'Test3\\newBase';//this type is Test3\newBase!
            }
        } while ($type = get_parent_class($type));
    }
    return gettype($value);
}


$obj = new newBase('12345');
$obj->setValue('text');

$obj2 = new \Test3\newView('9876');//the value 'O9876' is not octal notation
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

var_dump($obj2->getSave() == $obj3->getSave());

