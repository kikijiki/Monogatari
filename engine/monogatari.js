function readCookie(name)
{
    name += '=';
    for (var ca = document.cookie.split(/;\s*/), i = ca.length - 1; i >= 0; i--)
        if (!ca[i].indexOf(name))
            return ca[i].replace(name, '');
}

function setCookie(name, value)
{
    var date = new Date();
    
    date.setTime(date.getTime() + (365*24*60*60*1000));
    document.cookie = name + "=" + value + "; expires=" + date.toGMTString() + "; path=/";
}

function setLanguage(lang)
{
    setCookie("lang", lang);
    window.location.reload();
}