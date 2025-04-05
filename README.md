# CISC 332 Project

Heya, my name is Mohammed Sarhat, and this is my Database implementation with some CSS, HTML, and PHP.

Just a quick precursor, for this project, I'm utilizing a MVC architecture.

Throughout Queen's, this isn't really taught, and the for the sake of clarity, I thought it would be important to explain how this architecture works.

MVC is short-form for *Model View Controller*. MVC is an interface architecture that can be broken up into three parts. The *Model* manages the data, and it's manipulation. The *View* manages the presentation of the data. Finally, the *Controller* manages the user interaction with the data. The view and controller both refer to the model directly. 

The reason I've decided to go with this design choice is honestly quite simple as MVC provides a lot of benefits; multiple views, alternative interactions, and code reuse.

Edit: initially I went with wanting to create a project with MVC architecture, but over time, I've come to the conclusion that's probably not a great idea due to the time constraints. For this reason, I'll be utilizing a very simple architecture that essentially utilizes the following layout:

I'll be hosting each page on its own independent PHP file, that contains all the content of the page, and its logic.
