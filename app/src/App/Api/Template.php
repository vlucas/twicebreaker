<?php
namespace App\Api;
use Hyperspan;

class Template extends \Bullet\View\Template
{
    public $callbacks = array();

    protected $_data = array();
    protected $_links = array();
    protected $_fields = array();
    protected $_titleField;
    protected $_showPrimaryAction = true;

    // Single item view?
    protected $_itemView = false;

    public function __construct($data = array())
    {
        if($data instanceof Hyperspan\Response) {
            $format = new Hyperspan\Formatter\Siren($data);
            $data = $format->toArray();
        }

        if(!is_array($data)) {
            throw new \InvalidArgumentException("Argument 1 passed to " . __METHOD__ . " must be of the type array or Hyperspan\Response. " . gettype($data) . " given");
        }

        if(!isset($data['title'])) {
            throw new \InvalidArgumentException("Title not set. Please add a 'title' key to the \$data array.");
        }

        parent::__construct('/templates/api', array('title' => $data['title'], 'data' => $data));

        // Set path to current directory
        $this->path(__DIR__);

        // Store data
        $this->_data = $data;
    }

    /**
     * Load and return new view for partial
     *
     * @param string $template Template file to use
     * @param array $vars Variables to pass to partial
     * @return Bullet\View\Template
     */
    public function partial($template, array $vars = array())
    {
        $tpl = new parent($template, $vars);
        $tpl->layout(false);
        return $tpl->path(__DIR__ . '/templates/');
    }

    /**
     * Is view single-item view?
     *
     * @return boolean
     */
    public function itemView($res = null)
    {
        if($res !== null) {
            $this->_itemView = $res;
            return $this;
        }
        return $this->_itemView;
    }

    /**
     * Set field name to the primary title field
     */
    public function titleField($field = null)
    {
        if($field !== null) {
            $this->_titleField = $field;
            return $this;
        }
        return $this->_titleField;
    }

    /**
     * Array of $properties from Siren JSON
     *
     * @return array
     */
    public function properties()
    {
        $properties = array();
        if(isset($this->_data['properties'])) {
            $properties = $this->_data['properties'];
        }
        return $properties;
    }

    /**
     * Array of $entities from Siren JSON
     *
     * @return array
     */
    public function entities()
    {
        $entities = array();
        if(isset($this->_data['entities'])) {
            $entities = $this->_data['entities'];
        }
        return $entities;
    }

    /**
     * Array of error messages
     *
     * @return array
     */
    public function errors()
    {
        $errors = array();
        if(isset($this->_data['errors'])) {
            $errors = $this->_data['errors'];
        }
        return $errors;
    }

    /**
     * Set particular fields to display on the form
     */
    public function fields(array $fields = null)
    {
        if($fields !== null) {
            $this->_fields = $fields;
            return $this;
        }

        // Determine fields to be displayed if none are explicitly set
        if(!$this->_fields) {
            if(isset($this->_data['entities'][0]['properties']['@display'])) {
                $this->_fields = $this->_data['entities'][0]['properties']['@display'];
            } elseif(isset($this->_data['entities'][0]['properties'])) {
                $this->_fields = array_keys($this->_data['entities'][0]['properties']);
            }
        }
        return $this->_fields;
    }

    /**
     * Define callback to display content for field
     *
     * @return string
     */
    public function field($field, $callback)
    {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException("Second parameter must be valid callback or closure (got " . gettype($callback) . ")");
        }

        $this->callbacks['field'][$field] = $callback;
        return $this;
    }

    /**
     * Get links array and format them
     */
    public function links(array $links = null)
    {
        if($links !== null) {
            $this->_links = $links;
            return $this;
        }

        // Parse links and normalize data
        if(!$this->_links) {
            $links = array();

            if(isset($this->_data['links'])) {
                $links = $this->formatLinks($this->_data['links']);
            }

            $this->_links = $links;
        }
        return $this->_links;
    }

    /**
     * Get primary action described in actions to display them on page
     */
    public function primaryAction()
    {
        $primaryLink = false;
        $links = $this->actions();
        foreach($links as $linkRel => $link) {
            if($linkRel == 'self') { continue; }
            if($linkRel == 'add') { $primaryLink = $linkRel; }
            if($primaryLink !== null) { break; }
        }
        return $primaryLink;
    }

    /**
     * Get actions/forms described in 'actions' to display them on page
     */
    public function actions()
    {
        $actions = array();
        if(isset($this->_data['actions'])) {
            $actions = $this->formatLinks($this->_data['actions'], 'name');
        }
        return $actions;
    }

    /**
     * Ensure 'title' key is present and make 'rel' the array key
     */
    public function formatLinks(array $data, $field = 'rel')
    {
        $links = array();
        foreach($data as $link) {
            if(!isset($link[$field])) {
                throw new \InvalidArgumentException("Link rel must be set. Looking at link: " . var_export($link, true));
            }
            // Use first rel listed if array
            $linkRel = is_array($link[$field]) ? $link[$field][0] : $link[$field];

            // Ensure title is set
            if(!isset($link['title'])) {
                $link['title'] = ucwords(str_replace('_', ' ', $linkRel));
            }
            $links[$linkRel] = $link;
        }
        return $links;
    }

    /**
     * Is this response ONLY actions?
     *
     * @return boolean
     */
    public function isOnlyActions()
    {
        return (count($this->actions()) >= 1 && count($this->entities()) === 0 && count($this->properties()) === 0);
    }
}
