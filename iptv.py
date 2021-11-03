import requests
import base64
from urllib import parse
response = requests.get("https://luotuo.oss-cn-beijing.aliyuncs.com/list.txt")
response = str(base64.b64decode(response.text), "utf8")
with open("iptv.txt", "w", encoding="utf8") as f:
    iptv_list = parse.unquote(response).split("\n\n")
    f.write(parse.unquote("\n\n".join(iptv_list[:-2])))

    
    
