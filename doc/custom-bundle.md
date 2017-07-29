Setup a custom bundle
=====================

1. Create a new git repository for your custom angular service layer bundle
2. Create a new git repository for your ionic application
3. Call the generator. Follow the instructions of the generator and enter your data for custom setup.

```bash
bin/console bricks:generate:brick
```

4. Go to your custom host url as entered in the bricks setup process
5. That's it - Code on your own awesome project.

Open:

- wenn gemacht, npm und gulp nicht mehr direkt aufrufen sondern über selbige skripte
- bei der gelegenheit bin/client-name-project-name/configure!
- slugify!! strtolower(client-name).-.strtowloer(projectname): problem: was wenn spaces enthalten!!
  -> Stringy::slugify(bla) is besser, cp. https://github.com/danielstjules/Stringy#staticstringy