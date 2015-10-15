<?php
namespace werx\Core;

/**
 * Extension to Plates to work better with our routing and auto-sanitize view variables.
 */
class ViewEngine extends \League\Plates\Engine
{

	protected $transforms = [];

	/**
	 * The name of the template layout.
	 * @var string
	 */
	protected $layoutName;

	/**
	 * The data assigned to the template layout.
	 * @var array
	 */
	protected $layoutData = [];

	/**
	 * Create a new template.
	 * @param  string   $name
	 * @return Template
	 */
	public function make($name)
	{

		$template = new Template($this, $name, $this->transforms);
		if (!empty($this->layoutName)) {
			$template->setLayout($this->layoutName, $this->layoutData);
			unset($this->layoutName);
			unset($this->layoutData);
		}
		return $template;
	}

	/**
     * Assign data to template object.
     * @param  array $data
     * @return null
     */
	public function data(array $data)
	{
		$this->addData($data);
	}

	/**
     * Set the default layout.
     * @param  string $name
     * @param  array  $data
     * @return null
     */
	public function layout($name, array $data = array())
	{
		$this->layoutName = $name;
		$this->layoutData = $data;
	}

	/**
	 * Don't escape template variables with the specified name.
	 *
	 * @param $key
	 */
	public function unguard($key)
	{
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->transform($k, false);
			}
		} else {
			$this->transform($key, false);
		}
	}

	/**
	 * Apply the specified transform to the template variables.
	 *
	 * @param $key
	 */
	public function transform($field, $functions)
	{
		$this->transforms[$field] = $functions;
	}
}
