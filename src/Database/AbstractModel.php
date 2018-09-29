<?php
namespace HTR\Database;

use App\System\Configuration as cfg;
use HTR\Database\AdapterResults as result;
use HTR\Common\Json;
use Slim\Http\Response;

class AbstractModel
{

    private $data;
    private static $that;

    /**
     * Validate the inpute of request
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param \stdClass $data
     * @param string $jsonShemaFile
     * @return bool
     */
    protected static function inputValidate(\stdClass $data, string $jsonShemaFile): bool
    {
        $jsonSchema = cfg::JSON_SCHEMA . $jsonShemaFile;

        if (Json::validate($data, $jsonSchema)) {
            return true;
        }

        return false;
    }

    /**
     * Return the Response Object configured with common error
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param Response $response
     * @param \Exception $ex
     * @return Response
     */
    protected static function commonError(Response $response, \Exception $ex): Response
    {
        $data = [
            "message" => "Somethings are wrong.",
            "status" => "error"
        ];
        if (cfg::htrFileConfigs()->devmode ?? false) {
            $data['dev_error'] = $ex->getMessage();
        }
        return $response->withJson($data, 500);
    }

    /**
     * Initialize one new instance of AbstractModel
     * and configure it with the results of query
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param type $data
     * @return \App\System\AbstractModel
     */
    protected static function outputValidate($data): AbstractModel
    {
        if (!self::$that) {
            self::$that = new AbstractModel;
        }

        self::$that->setData(result::adapter($data));

        return self::$that;
    }

    /**
     * Verfify if one attribute exists into result set
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param type $name
     * @return bool
     */
    protected function attributeExists($name): bool
    {
        return isset($this->data, $name);
    }

    /**
     * Set the results
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param type $data
     */
    private function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Adding new attributes into results for response
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param \stdClass $obj
     * @param array $elements
     * @return \stdClass
     * @throws \Exception
     */
    private function addingAttribute(\stdClass $obj, array $elements)
    {
        foreach ($elements as $name => $value) {
            try {
                $obj->$name = is_callable($value) ? $value($obj) : $value;
            } catch (\Exception $ex) {
                throw new \Exception("Could not process attribute {$name}");
            }
        }
        return $obj;
    }

    /**
     * Interface to adding new attributes into results
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param mixed $name
     * @param mixed $value
     * @param bool $inAllElements
     * @return \self
     * @throws \Exception
     */
    final public function withAttribute($name, $value = null, bool $inAllElements = false): self
    {
        // insert or update one or more attributes into all elements
        if (is_array($this->data) && $inAllElements === true) {
            foreach ($this->data as $element) {
                if (is_array($name)) {
                    $element = $this->addingAttribute($element, $name);
                    continue;
                }
                if (!is_array($name) && is_callable($value)) {
                    $element->$name = $value($element);
                    continue;
                }
                $element->$name = $value;
            }

            return $this;
        } else {
            if ($inAllElements === true) {
                throw new \Exception("The results is not an array");
            }
        }

        // insert one or more attributes into Object result
        if (is_array($name)) {
            foreach ($name as $attributeKey => $attributeValue) {
                if (is_array($this->data)) {
                    $this->data[$attributeKey] = is_callable($attributeValue) ? $attributeValue($this->data) : $attributeValue;
                } else {
                    $this->data->$attributeKey = is_callable($attributeValue) ? $attributeValue($this->data) : $attributeValue;
                }
            }
            return $this;
        }

        // insert one attribute into Object result
        if (is_array($this->data)) {
            $this->data[$name] = is_callable($value) ? $value($this->data) : $value;
        } else {
            $this->data->$name = is_callable($value) ? $value($this->data) : $value;
        }

        return $this;
    }

    /**
     * Remove one attribute of result
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param mixed $name
     * @return \self
     */
    final public function withoutAttribute($name): self
    {

        // remove attributes when $name is a list (array) of attributes to be removed
        // remove attributes from root data value
        if (is_array($name)) {
            foreach ($name as $attribute) {
                if ($this->attributeExists($attribute)) {
                    unset($this->data->$attribute);
                }
            }
        }
        // remove attribute from root data value
        if (is_string($name) && $this->attributeExists($name)) {
            unset($this->data->$name);
        }
        // remove attributes from elements of data value
        if (is_array($this->data)) {
            foreach ($this->data as $value) {
                // remove just one attribute
                if (is_string($name) && isset($value->$name)) {
                    unset($value->$name);
                }
                // remove one or more attributes from elements of data value
                if (is_array($name)) {
                    foreach ($name as $attribute) {
                        if (is_string($attribute) && isset($value->$name)) {
                            unset($value->$attribute);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Return the configured results
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return mixed
     */
    final public function run()
    {
        return $this->data;
    }
}
