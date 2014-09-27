<?php
	/**
	* Function to get content of directory using scandir.
	* It excludes directories set in settings.json under exclude_dirs key.
	* Also exludes hidden folders.
	*
	* @param $dir - path to directory, default path set in settings.json under localhost_dir key
	* @return $dir_array - array with projects and projects info
	*/
	function get_dir_content($dir) {
		//Content of the given directory
	    $dir_content = scandir($dir);
	    $date_format = get_settings_option('date_format');
	    //String of directories that need to be excluded from printing
	    $exclude_dirs = get_settings_option('exclude_dirs');
	    $dir_array[] = array();

	    $i = 0;
	    foreach($dir_content as $dir_item) {
	        if($dir_item != '.' && $dir_item != '..' && strpos($exclude_dirs, $dir_item) === false && strpos($dir_item, '.') !== 0) {
	            if(is_dir($dir . '/' . $dir_item)) {
	            	$dir_array[$i]['name'] = $dir_item;
	            	$dir_array[$i]['link'] = '/' . $dir_item . '/';
	            	$dir_array[$i]['ctime'] = date($date_format, stat($dir_item)['ctime']);
	            	$dir_array[$i]['mtime'] = date($date_format, stat($dir_item)['mtime']);
	            	$dir_array[$i]['size'] = format_size(folder_size($dir . '/' . $dir_item));
	            	$i++;
	            }
	        }
	    }

	    return $dir_array;
	}

	/**
	* Function to create icon view from plain array returned from get_dir_content function.
	*
	* @param $dir_array - array returned by get_dir_content function
	* @return $icon_view - HTML format of icon view
	*/
	function display_icon_view($dir_array) {
		$html_format = '
			<div class="project">
				<a href="%s">
					<span class="left">%s</span>
					<span class="left">
						<h2>%s</h2>
						<span class="cdate">%s</span>
						<span class="mdate">%s</span>
						<span class="size">%s</span>
					</span>
				</a>
				<div class="clearfix"></div>
			</div>';

		$icon_view = '';
		for ($i = 0; $i < count($dir_array); $i++) {
			$folder_icon = ($dir_array[$i]['size'] == 'Empty') ? '<span class="icon"><img src="img/empty-folder.png" alt="Folder"></span>' : '<span class="icon"><img src="img/folder.png" alt="Folder"></span>';
			$icon_view .= sprintf($html_format, $dir_array[$i]['link'], $folder_icon, $dir_array[$i]['name'], $dir_array[$i]['ctime'], $dir_array[$i]['mtime'], $dir_array[$i]['size']);
		}

		$icon_view = '<div id="projects-grid">' . $icon_view . '</div>';

		return $icon_view;
	}

	/**
	* Function to create list/table view from plain array returned from get_dir_content function.
	*
	* @param $dir_array - array returned by get_dir_content function
	* @return $icon_view - HTML format of list/table view
	*/
	function display_list_view($dir_array) {
		$html_format = '
		<tr onclick="document.location = \'%s\';">
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
		</tr>';

		$table_view = '';
		for ($i = 0; $i < count($dir_array); $i++) {
			$table_view .= sprintf($html_format, $dir_array[$i]['link'], $dir_array[$i]['name'], $dir_array[$i]['ctime'], $dir_array[$i]['mtime'], $dir_array[$i]['size']);
		}

		$table_view =
		'<div id="projects-list">
			<table cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th>Project Name</th>
						<th>Create Date</th>
						<th>Modification Date</th>
						<th>Size</th>
					</tr>
				</thead>
				<tbody>' .
					$table_view .
				'</tbody>
			</table>
		</div>';

		return $table_view;
	}

	/**
	* Function for number of projects.
	*
	* @param $dir_content - array returned by get_dir_content function
	* @return - number of projects in $dir_content array
	*/
	function get_projects_number($dir_content) {
		return count($dir_content);
	}

	/**
	* Helper function for getting value from settings.json file.
	*
	* @param $option - key of which value you need to get
	* @return - specified value from settings.json file
	*/
	function get_settings_option($option) {
		$settings_file = file_get_contents("settings.json");
		$json = json_decode($settings_file, TRUE);
		return $json[$option];
	}

	/**
	* Helper function to format size of project folder.
	*
	* @param $size - size that needs to be formated
	* @return - formated size
	*/
	function format_size($size) {
		if ($size == 0) {
			return 'Empty';
		} else {
			$mod = 1024;
			$units = explode(' ','B KB MB GB TB PB');
			for ($i = 0; $size > $mod; $i++) {
				$size /= $mod;
			}

			return round($size, 2) . ' ' . $units[$i];
		}
	}

	/**
	* Helper function to calculate size of folder.
	*
	* @param $path - path to folder
	* @return $total_size - total size of specified folder
	*/
	function folder_size($path) {
	    $total_size = 0;
	    $files = scandir($path);
	    $cleanPath = rtrim($path, '/') . '/';

	    foreach($files as $t) {
	        if ($t != "." && $t != "..") {
	            $currentFile = $cleanPath . $t;
	            if (is_dir($currentFile)) {
	                $size = folder_size($currentFile);
	                $total_size += $size;
	            }
	            else {
	                $size = filesize($currentFile);
	                $total_size += $size;
	            }
	        }
	    }

	    return $total_size;
	}

	//Getting directory content to work with
	$dir_content = get_dir_content(get_settings_option('localhost_dir'));
?>
<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">
		<title>Localhost Project Folder</title>
		<meta name="description" content="Localhost Project Folder">
		<meta name="author" content="Tomas ZORVAN">
		<link rel="stylesheet" href="css/style.css">
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
	</head>

	<body>
		<div id="main">
			<div class="clearfix" style="height: 20px;"></div>
			<div id="header">
				<div class="left">
					<h1>Localhost Projects</h1>
				</div>
				<div class="right">
					<div class="info">
						<span>Display: </span>
						<a href="?display=icons">Icons</a>
						<a href="?display=list" class="last">List</a>
						<span>Number of projects: <?php echo get_projects_number($dir_content); ?></span>
					</div>
				</div>
			</div>
			<div class="clearfix" style="height: 20px;"></div>
			<?php
				if (isset($_GET['display'])) {
					if ($_GET['display'] == 'list') {
						echo display_list_view($dir_content);
					} else {
						echo display_icon_view($dir_content);
					}
				} else {
					echo display_list_view($dir_content);
				}
			?>
		</div>

	</body>
</html>