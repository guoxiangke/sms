<?php
/**
 * ssms
 */
require "php_serial.class.php";
// require "php_serial.class.php1";
function ssms_serial_init($com="COM3",$BaudRate=115200){
	$serial = new phpSerial;
	$serial->deviceSet($com);
	$serial->confBaudRate($BaudRate);
	// Then we need to open it
	$serial->deviceOpen();
	return $serial;
}

function ssms_get_result($serial){
	var_dump($serial->readPort());
}
/**
 * 
 本节,我们将利用XC-TC35模块来给指定手机号码发送一条英文短信。
这里,我们用到AT+CMGS指令来发送短信,发送全英文/数字短信的时候,我们先设置为:"GSM"字 符集(AT+CSCS="GSM"),文本模式(AT+CMGF=1)。
假设我们要给手机号:18765585827,发送一条短信,则发送:AT+CMGS="18765585827",
然后模块返回:>,此时我们输入我们需要发送的内容: XCKJ XC-TC35 MSG SEND TEST,注意, 此可以不用发送回车了。在发送完内容以后,最后以十六进制(HEX)格式单独发送(不用添加回车):1A (即0X1A)1,即可启动一次短信发送。
注1:0X1A,即“CTRL+Z”的键值,用于告诉TC35,要执行发送操作。另外还可以发送:0X1B,即“ESC”的键值, 用于告诉TC35,取消本次操作,不执行发送。
稍等片刻,在短信成功发送后,模块返回如:+CMGS: 156,的确认信息,表示短信成功发送,其中156 为模块内部的短信计数器,一般不用理会。
 */


function ssms_send_sms($telnum='10086',$text='cxll'){
	$serial = ssms_serial_init();
	// To write into
	$serial->sendMessage("AT+CSCS=\"GSM\"\n\r"); //GSM"字符集
	$serial->sendMessage("AT+CMGF=1\n\r"); //文本模式
	$serial->sendMessage("AT+cmgs=\"".$telnum."\"\n\r");
	$serial->sendMessage($text."\n\r");
	$serial->sendMessage(chr(26));
	ssms_clean_serial($serial);
}

/**
 * 
 首先,我们发送:AT+CMGF=1,设置为文本模式,
 然后发送:AT+CSCS="GSM",设置GSM字符集, 
 然后发送:AT+CNMI=2,1,设置新消息提示。接着,我们用别的手机发送一条中英文短信“兴创科技欢迎 您”到我们的模块上。
模块接收到短信后,会提示如:+CMTI: "SM",3,表明收到了新的短信,存放在SIM卡位置3。
然后, 我们发送AT+CMGR=3,即可读取该短信,
 */

function ssms_read_sms(){
	$serial = ssms_serial_init();
	$serial->sendMessage("AT+CMGF=1\n\r"); //文本模式
	$serial->sendMessage("AT+CSCS=\"GSM\"\n\r"); //GSM"字符集
	$serial->sendMessage("AT+CNMI=2,1\n\r"); //设置新消息提示
	//waitting msg coming... then show: +CMTI: "SM",3,
	var_dump('waitting msg coming...');
	sleep(30);
	var_dump('waitting go!...');
	var_dump(ssms_get_result($serial));
	$serial->sendMessage("AT+CMGR=3\n\r"); //即可读取该短信
	var_dump(ssms_get_result($serial));
	ssms_clean_serial($serial);
}

function ssms_clean_serial($serial){
	//wait for modem to send message
	sleep(7);
	$read=$serial->readPort();
	$serial->deviceClose();	
}

