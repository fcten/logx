<?php
/* 
 * LogX 博客系统 - 代码如诗
 * 
 * @copyright	LogX Team (http://logx.org/)
 * @license	GNU General Public License V2.0
 * 
 */

// Copy from JBlog.

class UploadLibrary extends Library {

	private $dir;			 //附件存放的绝对路径
	private $path;			 //附件存放的相对路径
	private $time;			 //自定义文件上传时间
	private $allow_types;	 //允许上传附件类型
	private $field;			 //上传控件名称
	private $maxsize;		 //最大允许文件大小，单位为KB

	private $thumb_width;    //缩略图宽度
	private $thumb_height;   //缩略图高度

	private $max_width;		//图片最大宽度
	private $max_height;	//图片最大高度

	private $watermark_file; //水印图片地址
	private $watermark_pos;  //水印位置
	private $watermark_trans;//水印透明度

	private $filetype; // 文件类型

	//构造函数
	//$types : 允许上传的文件类型 , $maxsize : 允许大小 ,  $field : 上传控件名称 , $time : 自定义上传时间
	public function __construct($types = 'jpg|gif|png', $maxsize = 1024, $field = 'attach', $time = '') {
		$this->allow_types = explode('|',strtolower($types));
		$this->maxsize = $maxsize * 1024;
		$this->field = $field;
		$this->time = $time ? $time : time();
		$this->setFileType();
	}

	//设置并创建文件具体存放的目录
	//$basedir  : 基目录，必须为物理路径
	//$filedir  : 自定义子目录，可用参数{y}、{m}、{d}
	public function set_dir($basedir,$filedir = '') {
		$dir = $basedir;
		!is_dir($dir) && @mkdir($dir,0777);
		if (!empty($filedir)) {
			$filedir = str_replace(array('{y}','{m}','{d}','\\'),array(gmdate('Y',$this->time),gmdate('m',$this->time),gmdate('d',$this->time),'/'),strtolower($filedir));
			$dirs = explode('/',$filedir);
			foreach ($dirs as $d) {
				!empty($d) && $dir .= $d.'/';
				if ( !is_dir($dir) ) {
					@mkdir($dir,0777);
					@fopen($dir.'index.html','wb');
				}
			}
		}
		$this->dir = $dir;
		$this->path = substr($dir,strlen($basedir));
	}

	//图片缩略图设置，如果不生成缩略图则不用设置
	//$width : 缩略图宽度 , $height : 缩略图高度
	public function set_thumb ($width = 0, $height = 0) {
		$this->thumb_width  = $width;
		$this->thumb_height = $height;
	}

	//设置自动缩放大小
	public function set_resize($width = 0, $height = 0) {
		$this->max_width = $width;
		$this->max_height = $height;
	}

	//图片水印设置，如果不生成添加水印则不用设置
	//$file : 水印图片 , $pos : 水印位置 , $trans : 水印透明度
	public function set_watermark ($file, $pos = 6, $trans = 80) {
		$this->watermark_file  = $file;
		$this->watermark_pos   = $pos;
		$this->watermark_trans = $trans;
	}

	/*----------------------------------------------------------------
	执行文件上传，处理完返回一个包含上传成功或失败的文件信息数组，
	其中：ogname	本地的文件名
		  name  为上传后自动生成的文件名，上传失败不存在该值
	      path  为附件的存放路径，不包括文件名，上传失败不存在该值
		  size  为附件大小，上传失败不存在该值
		  type  为附件的MIME类型
		  status  为状态标识，1表示成功，-1表示文件类型不允许，-2表示文件大小超出
	-----------------------------------------------------------------*/
	public function execute() {
		// 返回的文件信息
		$files = array(
			'ogname'    => '',
			'name'      => '',
			'path'      => '',
			'size'      => '',
			'type'      => '',
			'status'    => 1,
		);
		if ( !count( $_FILES ) ) return false;
		$field = $this->field;

		if( $_FILES[$field]['error'] != 0 ){
			switch( $_FILES[$field]['error'] ) {
				case 1:
					$files['status'] = -3;
					break;
				case 2:
				case 3:
				case 4:
					$files['status'] = -4;
					break;
			}
			return $files;
		}

		// 获取文件扩展名
		$fileext = $this->fileext( $_FILES[$field]['name'] );
		// 生成文件名
		$filename = gmdate( 'YmdHis', $this->time ) . mt_rand(1000000,9999999);
		// 文件大小
		$filesize = $_FILES[$field]['size'];
		// 限制上传格式
		$disarr = array('asa','asax','ascx','asxh','asmx','asp','aspx','axd','cer','cs','java','php','php3','php4','shtm','shtml');
		if ( in_array( $fileext, $disarr ) || !in_array( $fileext,$this->allow_types ) ) {
			$files['status'] = -1;
			return $files;
		}
		//文件大小超出
		if ( $filesize > $this->maxsize ) {
			$files['status'] = -2;
			return $files;
		}
		$files['ogname'] = trim( htmlspecialchars( $_FILES[$field]['name'] ) );
		$files['name']   = $filename;
		$files['path']   = $this->path;
		$files['size']   = $filesize;
		$files['ext']    = $fileext;

		//修正addslashes后win下路径多出转义导致文件无法上传的问题
		$_FILES[$field]['tmp_name'] = str_replace( '\\\\','\\', $_FILES[$field]['tmp_name'] );
		//保存上传文件并删除临时文件
		if ( is_uploaded_file( $_FILES[$field]['tmp_name'] ) ) {
			move_uploaded_file( $_FILES[$field]['tmp_name'], $this->dir.$filename.'.'.$fileext );
			//@unlink( $_FILES[$field]['tmp_name'] );

			if( function_exists( "mime_content_type" ) ) {
				$files['type'] = mime_content_type( $this->dir.$filename.'.'.$fileext );
			} elseif( isset( $this->filetype[$fileext] ) ) {
				$files['type'] = $this->filetype[$fileext];
			} else {
				$files['type'] = "application/octet-stream";
			}

			//对图片进行加水印和生成缩略图
			if ( in_array( $fileext, array('jpg','png','jpeg') ) ) {
				if ( $this->max_width || $this->max_height ) {
					$this->resize( $this->dir.$filename.'.'.$fileext );
					$files['size'] = filesize( $this->dir.$filename.'.'.$fileext );
				}
				if ( $this->thumb_width || $this->thumb_height ) {
					$this->create_thumb( $this->dir.$filename.'.'.$fileext, $this->dir.'thumb_'.$filename.'.'.$fileext );
				}
				if ( $this->watermark_file ) {
					$this->create_watermark( $this->dir.$filename.'.'.$fileext, $this->dir.'watermark_'.$filename.'.'.$fileext );
				}
			}
		}

		// 将附件信息写入数据库
		$meta = new MetaLibrary();
		$m['name'] = $files['ogname'];
		$m['alias'] = $this->path.$filename.'.'.$fileext;
		$m['description'] = $files['type'];
		$m['type'] = 3;
		$mid = $meta->addMeta( $m );
		// 把 pid 为 1000000000 的文章作为新上传的附件的暂居地。这样做并不好，但我想这应该够了。 
		$meta->addRelation( $mid, 1000000000 );

		$files['mid'] = $mid;

		return $files;
	}

	//创建缩略图,以相同的扩展名生成缩略图
	//$src_file : 来源图像路径 , $thumb_file : 缩略图路径
	private function create_thumb ($src_file,$thumb_file) {
		$t_width  = $this->thumb_width;
		$t_height = $this->thumb_height;

		if (!file_exists($src_file)) return false;

		$src_info = getImageSize($src_file);

		//如果来源图像小于或等于缩略图则拷贝源图像作为缩略图
		if ($src_info[0] <= $t_width && $src_info[1] <= $t_height) {
			if (!copy($src_file,$thumb_file)) {
				return false;
			}
			return true;
		}

		//按比例计算缩略图大小
		if ($src_info[0] - $t_width > $src_info[1] - $t_height) {
			$t_height = ($t_width / $src_info[0]) * $src_info[1];
		} else {
			$t_width = ($t_height / $src_info[1]) * $src_info[0];
		}

		//取得文件扩展名
		$fileext = $this->fileext($src_file);

		switch ($fileext) {
			case 'jpg' :
				$src_img = ImageCreateFromJPEG($src_file); break;
			case 'png' :
				$src_img = ImageCreateFromPNG($src_file); break;
			case 'gif' :
				$src_img = ImageCreateFromGIF($src_file); break;
		}

		//创建一个真彩色的缩略图像
		$thumb_img = @ImageCreateTrueColor($t_width,$t_height);

		//ImageCopyResampled函数拷贝的图像平滑度较好，优先考虑
		if (function_exists('imagecopyresampled')) {
			@ImageCopyResampled($thumb_img,$src_img,0,0,0,0,$t_width,$t_height,$src_info[0],$src_info[1]);
		} else {
			@ImageCopyResized($thumb_img,$src_img,0,0,0,0,$t_width,$t_height,$src_info[0],$src_info[1]);
		}

		//生成缩略图
		switch ($fileext) {
			case 'jpg' :
				ImageJPEG($thumb_img,$thumb_file); break;
			case 'gif' :
				ImageGIF($thumb_img,$thumb_file); break;
			case 'png' :
				ImagePNG($thumb_img,$thumb_file); break;
		}

		//销毁临时图像
		@ImageDestroy($src_img);
		@ImageDestroy($thumb_img);

		return true;

	}

	//为图片添加水印
	//$file : 要添加水印的文件
	private function create_watermark ($file) {

		//文件不存在则返回
		if (!file_exists($this->watermark_file) || !file_exists($file)) return;
		if (!function_exists('getImageSize')) return;
		
		//检查GD支持的文件类型
		$gd_allow_types = array();
		if (function_exists('ImageCreateFromGIF')) $gd_allow_types['image/gif'] = 'ImageCreateFromGIF';
		if (function_exists('ImageCreateFromPNG')) $gd_allow_types['image/png'] = 'ImageCreateFromPNG';
		if (function_exists('ImageCreateFromJPEG')) $gd_allow_types['image/jpeg'] = 'ImageCreateFromJPEG';

		//获取文件信息
		$fileinfo = getImageSize($file);
		$wminfo   = getImageSize($this->watermark_file);

		if ($fileinfo[0] < $wminfo[0] || $fileinfo[1] < $wminfo[1]) return;

		if (array_key_exists($fileinfo['mime'],$gd_allow_types)) {
			if (array_key_exists($wminfo['mime'],$gd_allow_types)) {
				
				//从文件创建图像
				$temp = $gd_allow_types[$fileinfo['mime']]($file);
				$temp_wm = $gd_allow_types[$wminfo['mime']]($this->watermark_file);

				//水印位置
				switch ($this->watermark_pos) {				
					case 1 :  //顶部居左
						$dst_x = 0; $dst_y = 0; break;				
					case 2 :  //顶部居中
						$dst_x = ($fileinfo[0] - $wminfo[0]) / 2; $dst_y = 0; break;				
					case 3 :  //顶部居右
						$dst_x = $fileinfo[0]; $dst_y = 0; break;				
					case 4 :  //底部居左
						$dst_x = 0; $dst_y = $fileinfo[1]; break;				
					case 5 :  //底部居中
						$dst_x = ($fileinfo[0] - $wminfo[0]) / 2; $dst_y = $fileinfo[1]; break;		
					case 6 :  //底部居右
						$dst_x = $fileinfo[0]-$wminfo[0]; $dst_y = $fileinfo[1]-$wminfo[1]; break;
					default : //随机
						$dst_x = mt_rand(0,$fileinfo[0]-$wminfo[0]); $dst_y = mt_rand(0,$fileinfo[1]-$wminfo[1]);
				}

				if (function_exists('ImageAlphaBlending')) ImageAlphaBlending($temp_wm,True); //设定图像的混色模式
				if (function_exists('ImageSaveAlpha')) ImageSaveAlpha($temp_wm,True); //保存完整的 alpha 通道信息

				//为图像添加水印
				if (function_exists('imageCopyMerge')) {
					ImageCopyMerge($temp,$temp_wm,$dst_x,$dst_y,0,0,$wminfo[0],$wminfo[1],$this->watermark_trans);
				} else {
					ImageCopyMerge($temp,$temp_wm,$dst_x,$dst_y,0,0,$wminfo[0],$wminfo[1]);
				}

				//保存图片
				switch ($fileinfo['mime']) {
					case 'image/jpeg' :
						@imageJPEG($temp,$file);
						break;
					case 'image/png' :
						@imagePNG($temp,$file);
						break;
					case 'image/gif' : 
						@imageGIF($temp,$file);
						break;
				}
				//销毁零时图像
				@imageDestroy($temp);
				@imageDestroy($temp_wm);
			}
		}
	}

	//调整头像大小
	private function resize($src_file) {
		$info = getImageSize($src_file);
		$width = $info[0];
		$height = $info[1];

		if ( $width <= $this->max_width && $height <= $this->max_height ) {
			return ;
		} else {

			if ( ($width - $this->max_width) > ($height - $this->max_height) ) {
				$this->max_height = ($this->max_width / $width) * $height; 
			} else {
				$this->max_width = ($this->max_height / $height) * $width;
			}
			
			$fileext = $this->fileext($src_file);

			switch ($fileext) {
				case 'jpg' :
				case 'jpeg' :
					$src_img = @ImageCreateFromJPEG($src_file); break;
				case 'png' :
					$src_img = @ImageCreateFromPNG($src_file); break;
				case 'gif' :
					$src_img = @ImageCreateFromGIF($src_file); break;
			}	

			$to_img = @ImageCreateTrueColor($this->max_width, $this->max_height);

			if (function_exists('imagecopyresampled')) {
				@ImageCopyResampled($to_img,$src_img,0,0,0,0,$this->max_width,$this->max_height,$width,$height);
			} else {
				@ImageCopyResized($to_img,$src_img,0,0,0,0,$this->max_width,$this->max_height,$width,$height);
			}
			
			switch ($fileext) {
				case 'jpg' :
				case 'jpeg' :
					ImageJPEG($to_img, $src_file); break;
				case 'png' :
					ImagePNG($to_img, $src_file); break;
				case 'gif' :
					ImageGIF($to_img, $src_file); break;
			}
			
			@ImageDestroy($src_img);
			@ImageDestroy($to_img);

			return true;
		}
		
	} 

	//获取文件扩展名
	public function fileext($filename) {
		return strtolower(substr(strrchr($filename,'.'),1,10));
	}

	// 设置已知 Mime
	private function setFileType() {
		$this->filetype['chm']='application/octet-stream'; 
		$this->filetype['ppt']='application/vnd.ms-powerpoint'; 
		$this->filetype['xls']='application/vnd.ms-excel'; 
		$this->filetype['doc']='application/msword'; 
		$this->filetype['exe']='application/octet-stream'; 
		$this->filetype['rar']='application/octet-stream'; 
		$this->filetype['js']="javascript/js"; 
		$this->filetype['css']="text/css"; 
		$this->filetype['hqx']="application/mac-binhex40"; 
		$this->filetype['bin']="application/octet-stream"; 
		$this->filetype['oda']="application/oda"; 
		$this->filetype['pdf']="application/pdf"; 
		$this->filetype['ai']="application/postsrcipt"; 
		$this->filetype['eps']="application/postsrcipt"; 
		$this->filetype['es']="application/postsrcipt"; 
		$this->filetype['rtf']="application/rtf"; 
		$this->filetype['mif']="application/x-mif"; 
		$this->filetype['csh']="application/x-csh"; 
		$this->filetype['dvi']="application/x-dvi"; 
		$this->filetype['hdf']="application/x-hdf"; 
		$this->filetype['nc']="application/x-netcdf"; 
		$this->filetype['cdf']="application/x-netcdf"; 
		$this->filetype['latex']="application/x-latex"; 
		$this->filetype['ts']="application/x-troll-ts"; 
		$this->filetype['src']="application/x-wais-source"; 
		$this->filetype['zip']="application/zip"; 
		$this->filetype['bcpio']="application/x-bcpio"; 
		$this->filetype['cpio']="application/x-cpio"; 
		$this->filetype['gtar']="application/x-gtar"; 
		$this->filetype['shar']="application/x-shar"; 
		$this->filetype['sv4cpio']="application/x-sv4cpio"; 
		$this->filetype['sv4crc']="application/x-sv4crc"; 
		$this->filetype['tar']="application/x-tar"; 
		$this->filetype['ustar']="application/x-ustar"; 
		$this->filetype['man']="application/x-troff-man"; 
		$this->filetype['sh']="application/x-sh"; 
		$this->filetype['tcl']="application/x-tcl"; 
		$this->filetype['tex']="application/x-tex"; 
		$this->filetype['texi']="application/x-texinfo"; 
		$this->filetype['texinfo']="application/x-texinfo"; 
		$this->filetype['t']="application/x-troff"; 
		$this->filetype['tr']="application/x-troff"; 
		$this->filetype['roff']="application/x-troff"; 
		$this->filetype['shar']="application/x-shar"; 
		$this->filetype['me']="application/x-troll-me"; 
		$this->filetype['ts']="application/x-troll-ts"; 
		$this->filetype['gif']="image/gif"; 
		$this->filetype['jpeg']="image/pjpeg"; 
		$this->filetype['jpg']="image/pjpeg"; 
		$this->filetype['jpe']="image/pjpeg"; 
		$this->filetype['ras']="image/x-cmu-raster"; 
		$this->filetype['pbm']="image/x-portable-bitmap"; 
		$this->filetype['ppm']="image/x-portable-pixmap"; 
		$this->filetype['xbm']="image/x-xbitmap"; 
		$this->filetype['xwd']="image/x-xwindowdump"; 
		$this->filetype['ief']="image/ief"; 
		$this->filetype['tif']="image/tiff"; 
		$this->filetype['tiff']="image/tiff"; 
		$this->filetype['pnm']="image/x-portable-anymap"; 
		$this->filetype['pgm']="image/x-portable-graymap"; 
		$this->filetype['rgb']="image/x-rgb"; 
		$this->filetype['xpm']="image/x-xpixmap"; 
		$this->filetype['txt']="text/plain"; 
		$this->filetype['c']="text/plain"; 
		$this->filetype['cc']="text/plain"; 
		$this->filetype['h']="text/plain"; 
		$this->filetype['html']="text/html"; 
		$this->filetype['htm']="text/html"; 
		$this->filetype['htl']="text/html"; 
		$this->filetype['rtx']="text/richtext"; 
		$this->filetype['etx']="text/x-setext"; 
		$this->filetype['tsv']="text/tab-separated-values"; 
		$this->filetype['mpeg']="video/mpeg"; 
		$this->filetype['mpg']="video/mpeg"; 
		$this->filetype['mpe']="video/mpeg"; 
		$this->filetype['avi']="video/x-msvideo"; 
		$this->filetype['qt']="video/quicktime"; 
		$this->filetype['mov']="video/quicktime"; 
		$this->filetype['moov']="video/quicktime"; 
		$this->filetype['movie']="video/x-sgi-movie"; 
		$this->filetype['au']="audio/basic"; 
		$this->filetype['snd']="audio/basic"; 
		$this->filetype['wav']="audio/x-wav"; 
		$this->filetype['aif']="audio/x-aiff"; 
		$this->filetype['aiff']="audio/x-aiff"; 
		$this->filetype['aifc']="audio/x-aiff"; 
		$this->filetype['swf']="application/x-shockwave-flash"; 
	}

}
?>
