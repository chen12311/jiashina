var imgSrc = []; //图片路径
var imgFile = []; //文件流
var imgName = []; //图片名字
//选择图片
function imgUpload(obj) {
    var oInput = '#' + obj.inputId;
    var imgBox = '#' + obj.imgBox;
    var btn = '#' + obj.buttonId;
    $(oInput).on("change", function() {
        if($("#inputBox span").html() == "上传成功") {
            $("#inputBox span").html("已上传");
            return false;
        }
        var fileImg = $(oInput)[0];
        var fileList = fileImg.files;
        for(var i = 0; i < fileList.length; i++) {
            var imgSrcI = getObjectURL(fileList[i]);
            imgName.push(fileList[i].name);
            imgSrc.push(imgSrcI);
            imgFile.push(fileList[i]);
        }
        addNewContent(imgBox);
    })
    $(btn).on('click', function() {
        if(imgFile == "") {
            $("#inputBox span").html("请选择图片");
            return false;
        }
        if(imgFile.length < '3') {
            $("#inputBox span").html("图片小于3张");
            return false;
        }
        if(imgFile.length > '20') {
            $("#inputBox span").html("图片超出20张");
            return false;
        }
        if($("#inputBox span").html() == "上传成功" || $("#inputBox span").html() == "已上传") {
            $("#inputBox span").html("已上传");
            return false;
        }
        $("#inputBox span").html("上传中");
        var data_data = obj.data;
        if(!limitNum(obj.num)) {
            $("#inputBox span").html("超过限制");
            return false;
        }
        //用formDate对象上传
        var fd = new FormData();

        for(var i = 0; i < imgFile.length; i++) {
            fd.append(data_data + "[]", imgFile[i]);
        }
        submitPicture(obj.upUrl, fd);
    })
}
//图片展示
function addNewContent(obj) {
    $(imgBox).html("");
    for(var a = 0; a < imgSrc.length; a++) {
        var oldBox = $(obj).html();
        $(obj).html(oldBox + '<div class="imgContainer"><img title=' + imgName[a] + ' alt=' + imgName[a] + ' src=' + imgSrc[a] + '><p onclick="removeImg(this,' + a + ')" class="imgDelete">删除</p></div>');
    }
}
//删除
function removeImg(obj, index) {
    imgSrc.splice(index, 1);
    imgFile.splice(index, 1);
    imgName.splice(index, 1);
    var boxId = "#" + $(obj).parent('.imgContainer').parent().attr("id");
    addNewContent(boxId);
}

//限制图片个数
function limitNum(num) {
    if(!num) {
        return true;
    } else if(imgFile.length > num) {
        return false;
    } else {
        return true;
    }
}
//上传(将文件流数组传到后台)
function submitPicture(url, data) {
    if(url && data) {
        $.ajax({
            type: "post",
            url: url,
            async: true,
            data: data,
            processData: false,
            contentType: false,
            success: function(dat) {
                $("#imgInput").html(dat);
                $("form").submit();
            }
        });
    } else {
        alert('上传失败');
    }
}
//关闭
function closePicture(obj) {
    $(obj).parent("div").remove();
}

//图片预览路径
function getObjectURL(file) {
    var url = null;
    if(window.createObjectURL != undefined) { // basic
        url = window.createObjectURL(file);
    } else if(window.URL != undefined) { // mozilla(firefox)
        url = window.URL.createObjectURL(file);
    } else if(window.webkitURL != undefined) { // webkit or chrome
        url = window.webkitURL.createObjectURL(file);
    }
    return url;
}