/**
 * Created by qiyq on 2017/2/27.
 */ window.onbeforeunload = function (event) {
    var ret = form1.ZAZFingerActivex.ZAZCloseOCX();
    //alert(ret);
};

var init = self.setInterval("clock()", 1);
function clock() {
    FillForm();
    window.clearInterval(init);
}
function FillForm() {
    form1.ZAZFingerActivex.OcxWidth = 256;
    form1.ZAZFingerActivex.OcxHeight = 288;
    form1.ZAZFingerActivex.width = form1.ZAZFingerActivex.OcxWidth;
    form1.ZAZFingerActivex.height = form1.ZAZFingerActivex.OcxHeight;
    return true;
}


function displaymessage1() {
    $("#ZAZFingerActivex").show();
    layer.msg('请录入指纹一', {icon: 6});
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spCharLen = form1.CharLen.value;
    var spTimeOut = 1000;
    form1.fingerCode1.value = "";
    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;
    form1.ZAZFingerActivex.CharLen = spCharLen;
    form1.ZAZFingerActivex.FingerCode = "";
    form1.ZAZFingerActivex.TimeOut = spTimeOut;
    form1.ZAZFingerActivex.ZAZSetIMG(256, 288);
    var mesg = form1.ZAZFingerActivex.ZAZGetImgCode();
    if (mesg == "0") {
        form1.fingerCode1.value = form1.ZAZFingerActivex.FingerCode;
        form1.finger_data.value = form1.ZAZFingerActivex.FingerCode;
    }
    else {
        form1.fingerCode1.value = "";
    }

}

function displaymessage2() {
    $("#ZAZFingerActivex2").show();
    layer.msg('请录入指纹二', {icon: 6});
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spCharLen = form1.CharLen.value;
    var spTimeOut = 1000;
    form1.fingerCode2.value = "";
    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;
    form1.ZAZFingerActivex.CharLen = spCharLen;
    form1.ZAZFingerActivex.FingerCode = "";
    form1.ZAZFingerActivex.TimeOut = spTimeOut;
    form1.ZAZFingerActivex.ZAZSetIMG(256, 288);
    var mesg = form1.ZAZFingerActivex.ZAZGetImgCode();
    if (mesg == "0") {
        form1.fingerCode2.value = form1.ZAZFingerActivex.FingerCode;
        form1.finger_data2.value = form1.ZAZFingerActivex.FingerCode;
    }
    else {
        form1.fingerCode2.value = "";
    }

}

function displaymessage3() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spCharLen = form1.CharLen.value;
    var spTimeOut = 5;
    form1.fingerCode3.value = "";
    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;
    form1.ZAZFingerActivex.CharLen = spCharLen;
    form1.ZAZFingerActivex.FingerCode = "";
    form1.ZAZFingerActivex.TimeOut = spTimeOut;
    var mesg = form1.ZAZFingerActivex.ZAZRegFinger();
    if (mesg == "0") {
        form1.fingerCode3.value = form1.ZAZFingerActivex.FingerCode;
    }
    else {
        form1.fingerCode3.value = "";
        alert(mesg);
    }

}

function displaybmpbase64() {
    form1.ZAZFingerActivex.ZAZSetIMG(256, 288);
    var ret = form1.ZAZFingerActivex.GetImgBase64();
    if (ret == 1) {
        form1.fingerbmpbase64.value = form1.ZAZFingerActivex.Bmpbase64;
        form1.ZAZFingerActivex.SetBase64Img(form1.fingerbmpbase64.value, "D:\dddd.bmp")
    }
    else {
        form1.fingerbmpbase64.value = ret;
    }
}

function Match() {
    var spSrc = "";
    var spDst = "";
    var spResult = 0;
    spSrc = form1.fingerCode1.value;
    spDst = form1.fingerCode2.value;
    spResult = form1.ZAZFingerActivex.ZAZMatch(spSrc, spDst);
    form1.getResult.value = spResult;
}

function SaveToFile() {
    var spFileName;
    spFileName = form1.FileName.value;
    form1.ZAZFingerActivex.ZAZSaveImg(spFileName);
    alert(spFileName);
}

function SaveToFilecolor() {

    var spFileName, spFileNamec, bmpcolor;
    var spFileName = form1.FileName.value;
    var spFileNamec = form1.FileNamec.value;
    var bmpcolor = form1.FileNamect;

    var spResult = form1.ZAZFingerActivex.ZAZCRATEBMP(spFileName, spFileNamec, 0);
    alert(spResult);
}


function WriteNote() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;

    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;

    var spNotePage, spContent, spResult;
    spNotePage = form1.eNotePage.value;
    spContent = form1.eWriteContent.value;

    spResult = form1.ZAZFingerActivex.ZAZWriteInfo(spNotePage, spContent);
    if (spResult !== 0) {
        alert("写入事本失败");
    }
    else {
        alert("写入事本成功");
    }
}

function ReadNote() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;

    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;

    var spNotePage, spContent;
    spNotePage = form1.eNotePage.value;
    spContent = '';

    spContent = form1.ZAZFingerActivex.ZAZReadInfo(spNotePage);
    form1.eReadContent.value = spContent;

}

function Addfp() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spfpno = form1.FPno.value;
    var spfpchar = form1.fingerCode1.value;

    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;

    var spResult = "";
    spResult = form1.ZAZFingerActivex.ZAZADDFinger(spfpno, spfpchar);
    alert(spResult);
}
function Delfp() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spfpno = form1.FPdel.value;


    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;

    var spResult = "";
    spResult = form1.ZAZFingerActivex.ZAZDelFinger(spfpno);
    alert(spResult);
}

function Delallfp() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;


    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;

    var spResult = "";
    spResult = form1.ZAZFingerActivex.ZAZEmptyFinger();
    alert(spResult);
}


function SearchFp() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spTimeOut = 5;

    var spfpstart = form1.FPstart.value;
    var spfpend = form1.FPend.value;

    form1.ZAZFingerActivex.spDeviceType = spDeviceType;
    form1.ZAZFingerActivex.spComPort = spComPort;
    form1.ZAZFingerActivex.spBaudRate = spBaudRate;
    form1.ZAZFingerActivex.TimeOut = spTimeOut;

    var spResult = "";

    spResult = form1.ZAZFingerActivex.ZAZSearchFinger(spfpstart, spfpend);
    if (spResult == "0") {
        var fpidd = form1.ZAZFingerActivex.SearchID;
        alert("搜索到相同指纹ID：" + fpidd);
    }
    else {
        alert("搜索失败");
    }

}
function Setsoundled() {
    var spDeviceType = form1.DeviceType.value;
    var spComPort = form1.ComPort.value;
    var spBaudRate = form1.BaudRate.value;
    var spledsound = form1.ledsound.value;
    var spcontrol = form1.control.value;
    var spResult = "";

    spResult = form1.ZAZFingerActivex.ZAZLEDSound(spledsound, spcontrol);
    if (spResult == "0") {
        alert("成功");
    }
    else {
        alert("失败");
    }
}