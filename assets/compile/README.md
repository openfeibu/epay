## Summer-Doc 接口文档管理工具

#### 简单的MarkdownApi管理工具:

* 左侧目录树: dtree.js (2004, 无依赖)
* Markdown转html: [parsedown(无安全校验速度快)](http://parsedown.org/)
* 遍历md源文件夹: 自己实现的利用后根序遍历算法读取文件夹内所有文件的PHP工具

#### 用法:
* 将md文件放进src文件中, 其文件和目录的命名就是最终生成树型目录中的名字
* 用PHP解释程序执行 compile.php 文件 (windows下建议用php7+: path/to/php.exe compile.php), 这一步会将md文件解析成html文件, 放到_book目录里
* Nginx/Apache 服务器
    * 建一个虚拟机 (例如：doc.hearu.top)
    * 将虚拟机的根目录指向_book
    * 虚拟机的入口文件设置为index.html
    * 启动服务器, 在浏览器里访问 doc.hearu.top 就可以了 (本地搭建需要修改hosts文件)

#### 小技巧: 
* 给git 加一个pre-commit hook, 将执行PHP编译md文件的命令放在 提交前 的时候执行, 这样就不用每次都手动执行编译命令了
* 给git 加一个post-push hook, 可以将_book作为git仓库, 提交(push)后自动部署到服务器根目录下
* code, table 的样式(github风格)已经写好了, 在 _book/dtree/my.css，修改后不用重新编译

#### 说明:

* 程序每次编译都会把编译时间记录下来, 下次编译的时候如果该文件没有修改, 就不会再编译了, 删掉记录时间用的那个文件(last_compile_time.log)就可以全部重新编译了
* 600多个md文件秒杀, 效率很高
* [总结](https://segmentfault.com/a/1190000013051771)
