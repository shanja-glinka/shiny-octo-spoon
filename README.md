# shiny-octo-spoon

1. Clone repo
```bash
git clone --recurse-submodules https://github.com/shanja-glinka/shiny-octo-spoon.git
```

2. Build Docker
```bash
docker-compose -f docker-compose.yml up -d
```

3. Run Startup Script
```bash
docker exec -it shiny-octo-spoon-app-1 /bin/bash -c /startupScript.sh
```

4. <br>

Project is available on [localhost](http://localhost:8800)<br>
PhpMyAdmin is [here](http://localhost:8081/)<br>
Access is **[root: root]**<br>
