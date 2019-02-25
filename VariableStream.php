<?php 
class VariableStream
{
	private $varname;

	private $position = 0;

	public function stream_open($path, $mode, $options, $opened_path) : bool
	{
		$this->varname = parse_url($path)['host'];
		return true;
	}

	public function stream_read($count)
	{
		$ret = substr($GLOBALS[$this->varname], $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}

	public function stream_write($data)
	{
		$left = substr($GLOBALS[$this->varname], 0, $this->position);
		$length = strlen($data);
		$right = substr($GLOBALS[$this->varname], $this->position + $length);
		$GLOBALS[$this->varname] = $left . $data . $right;
		$this->position += $length;
		return $length;
	}

	public function stream_tell()
	{
		return $this->position;
	}

	public function stream_seek($offset, $whence)
	{
		switch ($whence) {
			case SEEK_SET:
				if($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0) {
					$this->position = $offset;
					return true;
				}
				return false;

			case SEEK_CUR:
				$this->position += $offset;
				return true;


			case SEEK_END:
				if(($length = (strlen($GLOBALS[$this->varname]) + $offset)) >= 0) {
					$this->position = $length;
					return true;
				} else 
					return false;

			default:
				return false;
		}
	}

	public function stream_eof()
	{
	    return $this->position >= strlen($GLOBALS[$this->varname]);
	}

	public function unlink($path)
	{
		$varname = parse_url($path)['host'];
		if(isset($GLOBALS[$varname])) unset($GLOBALS[$varname]);
		return true;
	}
}

// $file = fopen('./1.txt', 'r+');
// fseek($file, 2);
// // var_dump(fread($file, 10));

// fwrite($file, 'abc'); //fwrite行为会覆盖指针后面的字符

stream_wrapper_register('var', VariableStream::class);

$myvar = "";

$fp = fopen("var://myvar", "r+");

fwrite($fp, "line1\n");
fwrite($fp, "line2\n");
fwrite($fp, "line3\n");

rewind($fp);
while (!feof($fp)) {
    echo fgets($fp);
}
fclose($fp);
var_dump($myvar);

unlink("var://myvar");

var_dump(isset($myvar));