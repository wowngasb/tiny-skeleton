<?php
/**
*--------------------------------------------------------------
* 验证码类
*--------------------------------------------------------------
* 最后修改时间 2012-1-10 Leon
* @version 1.0
* @author Leon(tmkook@gmail.com)
* @date 2012-1-10
*--------------------------------------------------------------
*/
class captcha
{
	//验证码字符
	protected $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	//生成的验证码文字
	protected $code_char;

	//使用字体
	protected $font;

	//字体颜色
	protected $font_color;

	//字体大小
	protected $font_size = 18;

	//验证码文字长度
	protected $lenth = 4;

	//画布宽
	protected $im_w = 98;

	//画布高
	protected $im_h = 32;

	//图像对象
	protected $im;
	
	function __construct(){
		$this->setFont(dirname(__FILE__).'/bookman.ttf');
	}

	/**
	* 设置验证码的随机字符
	*
	* @param string or array $code 随机字符如abc或array(1,2,3)
	*/
	function setCode($code){
		$this->code = $code;
	}

	/**
	* 设置验证码字体
	*
	* @param string $font 字体路径
	*/
	function setFont($font){
		if( ! file_exists($font)) throw new Exception("字体不存在");
		$this->font = $font;
	}

	/**
	* 设置验证码长度
	*
	* @param integer $len 长度
	*/
	function setLenth($len){
		if(!is_numeric($len)) throw new Exception("长度必须是正整数");
		$this->lenth = intval($len);
	}

	/**
	* 设置验证码大小
	*
	* @param integer $w 图像宽
	* @param integer $h 图像高
	*/
	function setImSize($w,$h){
		if($w <= 0 || $h <= 0 || $w < $h) throw new Exception("图像尺寸必须大于0且宽度不能小于高度");
		$this->im_w = $w;
		$this->im_h = $h;
		$this->font_size = $w/$this->lenth*0.8; //根据图像尺寸调整字体大小
	}

	/**
	* 创建验证画布
	*/
	function create(){
		 //验证码画布
		 $this->im = imagecreate($this->im_w,$this->im_h);
		 imagecolorallocate($this->im, 255, 255, 255); //画布背景
		 //验证码字符
		 $code_len = strlen($this->code)-1;
		 for($i=0; $i<$this->lenth; ++$i){
			 $this->code_char[] = $this->code[mt_rand(0,$code_len)];
		 }
		 //字体颜色
		 $this->font_color = imagecolorallocate($this->im, mt_rand(0,150), mt_rand(0,150), mt_rand(0,150)); //字体颜色
	}

	/**
	* 获取验证码内容
	* @param bool $is_lower 是否转换为小写
	* @return string
	*/
	function getCodeChar($is_lower=TRUE){
		return implode('',$this->code_char);
	}
 
	/**
	* 绘制干扰燥点
	*/
    function drawNoise() {
        for($i = 0; $i < 6; ++$i){
            $color = imagecolorallocate($this->im,mt_rand(120,200),mt_rand(120,200),mt_rand(120,200));//杂点颜色
            for($j = 0; $j < 2; ++$j){
                imagestring($this->im,6,mt_rand(-10,$this->im_w),mt_rand(-10,$this->im_h),chr(mt_rand(65,120)),$color);
            }
        }
    }

	/**
	* 绘制干扰线
	* @param integer $thick 干扰线粗
	*/
    function drawCurve($thick=2){
        $range = mt_rand(1, $this->im_h/6);//弯曲度
        $offset_y = mt_rand(-$this->im_h/4, $this->im_h/2);//干扰线Y轴位置
        $offset_x = mt_rand(0, $this->im_h*0.8); //干扰线X轴位置
        $cycle = mt_rand($this->im_h*1.5, $this->im_w*3); //像素的间距
        $w = (6 * M_PI)/$cycle; //弯曲数量
        $px1 = rand(0,$this->im_w*0.2);//干扰线起始位置 
        $px2 = mt_rand($this->im_w*0.5, $this->im_w);//干扰线结束位置
        for($px=$px1; $px<=$px2; $px+=0.01){
			$py = $range * sin($w*$px + $offset_x)+ $offset_y + $this->im_h/2;
			$i = $thick;
			while ($i > 0){
				imagesetpixel($this->im, $px + $i, $py + $i, $this->font_color);
				$i--;
			}
        }
	}

	/**
	* 输出验证码
	*/
	function display(){
        foreach($this->code_char as $key=>$char){
			$gap = $key*$this->font_size+$this->im_w/$this->lenth/3;
            imagettftext($this->im, $this->font_size, mt_rand(-$this->im_h/2,$this->im_h/2), $gap, $this->font_size*1.5, $this->font_color, $this->font, $char);
        }
		header('Pragma: no-cache');
        header("content-type: image/jpeg");
		imagejpeg($this->im);
        imagedestroy($this->im);
	}
	
	/**
	* 转为base64图像资源
	*/
	function getBase64(){
        foreach($this->code_char as $key=>$char){
			$gap = $key*$this->font_size+$this->im_w/$this->lenth/3;
            imagettftext($this->im, $this->font_size, mt_rand(-$this->im_h/2,$this->im_h/2), $gap, $this->font_size*1.5, $this->font_color, $this->font, $char);
        }
		ob_start();
		imagejpeg($this->im);
		$data =ob_get_contents();
		ob_end_clean();
        imagedestroy($this->im);
		return 'data:image/jpeg;base64,'.base64_encode($data);
	}

}

//end Captcha.php
