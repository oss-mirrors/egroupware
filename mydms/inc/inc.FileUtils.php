<?

function renameFile($old, $new)
{
	return rename($old, $new);
}

function removeFile($file)
{
	return unlink($file);
}

function copyFile($source, $target)
{
	return copy($source, $target);
}

function moveFile($source, $target)
{
	if (!copyFile($source, $target))
		return false;
	return removeFile($source);
}

function renameDir($old, $new)
{
	return rename($old, $new);
}

function makeDir($path)
{
	return mkdir($path, 0755);
}

function removeDir($path)
{
	$handle = opendir($path);
	while ($entry = readdir($handle) )
	{
		if ($entry == ".." || $entry == ".")
			continue;
		else if (is_dir($path . $entry))
		{
			if (!removeDir($path . $entry . "/"))
				return false;
		}
		else
		{
			if (!unlink($path . $entry))
				return false;
		}
	}
	closedir($handle);
	return rmdir($path);
}

function copyDir($sourcePath, $targetPath)
{
	if (mkdir($targetPath, 0777))
	{
		$handle = opendir($sourcePath);
		while ($entry = readdir($handle) )
		{
			if ($entry == ".." || $entry == ".")
				continue;
			else if (is_dir($sourcePath . $entry))
			{
				if (!copyDir($sourcePath . $entry . "/", $targetPath . $entry . "/"))
					return false;
			}
			else
			{
				if (!copy($sourcePath . $entry, $targetPath . $entry))
					return false;
			}
		}
		closedir($handle);
	}
	else
		return false;
	
	return true;
}

function moveDir($sourcePath, $targetPath)
{
	if (!copyDir($sourcePath, $targetPath))
		return false;
	return removeDir($sourcePath);
}

//To-DO: fehler abfangen
function getSuitableDocumentDir()
{
	GLOBAL $settings;
	
	$maxVal = 0;
	
	$handle = opendir($settings->_contentDir);
	while ($entry = readdir($handle))
	{
		if ($entry == ".." || $entry == ".")
			continue;
		else if (is_dir($settings->_contentDir . $entry))
		{
			$num = intval($entry);
			if ($num >= $maxVal)
				$maxVal = $num+1;
		}
	}
	$name = "" . $maxVal . "";
	while (strlen($name) < 5)
		$name = "0" . $name;
	return $name . "/";
}
?>