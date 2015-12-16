<?php
namespace Cantiga\CoreBundle\Generator;

/**
 * Description of Generator
 *
 * @author Tomasz Jędrzejewski
 */
abstract class Generator
{
	private $location;
	private $namespace;
	/**
	 * @var ReportInterface 
	 */
	protected $reportIfc;
	
	public function __construct(ReportInterface $reportIfc)
	{
		$this->reportIfc = $reportIfc;
	}
	
	public function getLocation()
	{
		return $this->location;
	}

	public function getNamespace()
	{
		return $this->namespace;
	}

	public function setLocation($location)
	{
		$this->location = $location;
		return $this;
	}

	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
	
	abstract public function generate();

	final protected function genNamespace($ns)
	{
		return $this->namespace.'\\'.$ns;
	}
	
	final protected function genTableRepository()
	{
		$parts = explode('\\', ltrim($this->namespace, '\\'));
		if (isset($parts[2])) {
			return str_replace('Bundle', 'Tables', $parts[2]);
		} else {
			return str_replace('Bundle', 'Tables', $parts[1]);
		}
	}
	
	final protected function genBundleName()
	{
		$firstBackslash = strpos($this->namespace, '\\');
		return str_replace('\\', '', substr($this->namespace, $firstBackslash + 1));
	}
	
	final protected function createDirectory($dir)
	{
		if (is_dir($this->location.'/'.$dir)) {
			$this->reportIfc->reportStatus('<error>Directory '.$this->location.'/'.$dir.' already exists!</error>');
		} else {
			$this->reportIfc->reportStatus('<info>Generating directory \''.$this->location.'/'.$dir.'\'...</info>');
			mkdir($this->location.'/'.$dir, 0777, true);
		}
	}
	
	final protected function save($file, $content)
	{
		if (file_exists($this->location.'/'.$file)) {
			$this->reportIfc->reportStatus('<error>File '.$this->location.'/'.$file.' already exists!</error>');
		} else {
			$this->reportIfc->reportStatus('<info>Generating file \''.$this->location.'/'.$file.'\'...</info>');
			file_put_contents($this->location.'/'.$file, $content);
		}
	}
}
