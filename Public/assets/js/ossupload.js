
var accessid = '',
    accesskey = '',
    host = '',
    policyBase64 = '',
    signature = '',
    callbackbody = '',
    ocallbackbody = '',
    filename = '',
    key = '',
    expire = 0,
    g_object_name = '',
    g_object_name_type = '',
    now = timestamp = Date.parse(new Date()) / 1000;

function send_request()
{
    var xmlhttp = null;
    if (window.XMLHttpRequest)
    {
        xmlhttp=new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  
    if (xmlhttp!=null)
    {
        var serverUrl = geturl;
        xmlhttp.open( "GET", serverUrl, false );
        xmlhttp.send( null );
        return xmlhttp.responseText;
    }
    else
    {
        alert("Your browser does not support XMLHTTP.");
    }
};

function check_object_radio() {
    var tt = document.getElementsByName('myradio');
    for (var i = 0; i < tt.length ; i++ )
    {
        if(tt[i].checked)
        {
            g_object_name_type = tt[i].value;
            break;
        }
    }
}

function get_signature()
{
    //可以判断当前expire是否超过了当前时间,如果超过了当前时间,就重新取一下.3s 做为缓冲
    now = timestamp = Date.parse(new Date()) / 1000; 
    if (expire < now + 3)
    {
        var body = send_request()
        var obj = eval ("(" + body + ")");
        host = obj['host'];
        policyBase64 = obj['policy'];
        accessid = obj['accessid'];
        signature = obj['signature'];
        expire = parseInt(obj['expire']);
        callbackbody = obj['callback'];
        ocallbackbody = callbackbody;
        key = obj['dir'];
        return true;
    }
    return false;
};

function random_string(len) {
　　len = len || 32;
　　var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';   
　　var maxPos = chars.length;
　　var pwd = '';
　　for (i = 0; i < len; i++) {
    　　pwd += chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

function get_suffix(filename) {
    var pos = filename.lastIndexOf('.')
    var suffix = ''
    if (pos != -1) {
        suffix = filename.substring(pos)
    }
    return suffix;
}

function calculate_object_name(filename)
{
    if (g_object_name_type == 'local_name')
    {
        g_object_name += "${filename}"
    }
    else if (g_object_name_type == 'random_name')
    {
        var suffix = get_suffix(filename)
        g_object_name = key + random_string(10) + suffix
    }
    return ''
}

function get_uploaded_object_name(filename)
{
    if (g_object_name_type == 'local_name')
    {
        var tmp_name = g_object_name
        tmp_name = tmp_name.replace("${filename}", filename);
        return tmp_name
    }
    else if(g_object_name_type == 'random_name')
    {
        return g_object_name
    }
}

function set_custom_info(id) {
    var albumSelect = document.getElementById('album');
    var album = albumSelect.options[albumSelect.selectedIndex].value;
    var descript = $("#"+id+" textarea").val();
    var pub = $("#"+id+" input:checkbox").is(':checked');
    callbackbody = window.atob(ocallbackbody);
    callbackbody = callbackbody.replace("${album}", album);
    callbackbody = callbackbody.replace("${description}", descript);
    callbackbody = callbackbody.replace("${public}", pub);
    console.log(callbackbody);
    callbackbody = window.btoa(callbackbody);

}

function set_upload_param(up, filename, ret)
{
    if (ret == false)
    {
        ret = get_signature()
    }
    g_object_name = key;
    if (filename != '') { suffix = get_suffix(filename)
        calculate_object_name(filename)
    }
    var new_multipart_params = {
        'key' : g_object_name,
        'policy': policyBase64,
        'OSSAccessKeyId': accessid, 
        'success_action_status' : '200', //让服务端返回200,不然，默认会返回204
        'callback' : callbackbody,
        'signature': signature,
    };

    up.setOption({
        'url': host,
        'multipart_params': new_multipart_params
    });

    up.start();
}

var uploader = new plupload.Uploader({
	runtimes : 'html5,flash,silverlight,html4',
	browse_button : 'selectfiles',
    //multi_selection: false,
	container: document.getElementById('container'),
	flash_swf_url : 'Moxie.swf',
	silverlight_xap_url : 'Moxie.xap',
    url : 'http://oss.aliyuncs.com',

    filters: {
        mime_types : [ //只允许上传图片和zip文件
        { title : "Image files", extensions : "jpg,gif,png,bmp" }, 
        { title : "Zip files", extensions : "zip,rar" }
        ],
        max_file_size : '10mb', //最大只能上传10mb的文件
        prevent_duplicates : true //不允许选取重复文件
    },

	init: {
		PostInit: function() {
			document.getElementById('ossfile').innerHTML = '';
			document.getElementById('postfiles').onclick = function() {
                set_upload_param(uploader, '', false);
                return false;
			};
		},

		FilesAdded: function(up, files) {
			plupload.each(files, function(file) {
                var fheader = '<div id="' + file.id + '">';
                var ffilename = '<h3>' + file.name + ' (' + plupload.formatSize(file.size) + ')</h3>';
                var fpublic = '<input type="checkbox" name="public" id="public_' + file.id+ '">'
                            + '<label for="public_' + file.id + '">Public</label>';
                var ftext = '<textarea></textarea>';
                var fmessage = '<b></b>';
                var fprogress = '<div class="progress"><div class="progress-bar" style="width: 0%"></div></div>';
                var ffooter = '</div>';
				//document.getElementById('ossfile').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size)
                //    + ')<input type="checkbox" name="public"><label>Public</label></form><br /><textarea></textarea><b></b>'
				//+'<div class="progress"><div class="progress-bar" style="width: 0%"></div></div>'
				//+'</div>';
                document.getElementById('ossfile').innerHTML += fheader + ffilename + fpublic + ftext + fmessage + fprogress + ffooter;
			});
		},

		BeforeUpload: function(up, file) {
            check_object_radio();
            set_custom_info(file.id);
            set_upload_param(up, file.name, true);
        },

		UploadProgress: function(up, file) {
			var d = document.getElementById(file.id);
			d.getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
            var prog = d.getElementsByTagName('div')[0];
            var progLength = prog.offsetWidth;
			var progBar = prog.getElementsByTagName('div')[0]
			progBar.style.width= progLength/100*file.percent+'px';
			progBar.setAttribute('aria-valuenow', file.percent);
		},

		FileUploaded: function(up, file, info) {
            if (info.status == 200)
            {
                var imgUrl = 'http://img-ali.yinger650.com/' + get_uploaded_object_name(file.name);
                var recallResponse = info.response;
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = 'Upload to OSS success, image url is <br> '
                    + '<a href="' + imgUrl + '">' + imgUrl + '</a>';
            }
            else if (info.status == 203)
            {
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = 'Upload to OSS success, but failed to visit recall server : <br>' + info.response;
            }
            else
            {
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = info.response;
            } 
		},

		Error: function(up, err) {
            if (err.code == -600) {
                document.getElementById('console').appendChild(document.createTextNode("\n选择的文件太大了,可以根据应用情况，在upload.js 设置一下上传的最大大小"));
            }
            else if (err.code == -601) {
                document.getElementById('console').appendChild(document.createTextNode("\n选择的文件后缀不对,可以根据应用情况，在upload.js进行设置可允许的上传文件类型"));
            }
            else if (err.code == -602) {
                document.getElementById('console').appendChild(document.createTextNode("\n这个文件已经上传过一遍了"));
            }
            else 
            {
                document.getElementById('console').appendChild(document.createTextNode("\nError xml:" + err.response));
            }
		}
	}
});


uploader.init();
