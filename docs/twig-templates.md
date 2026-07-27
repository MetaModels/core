# Twig-Templates in MetaModels

Ab MetaModels 2.5 kann jedes MetaModels-Template zusätzlich als **Twig-Template** angeboten
werden. Existiert für ein Template eine Twig-Variante, hat sie **Vorrang** vor dem klassischen
`.html5`-PHP-Template – analog zu Contao Core. Fehlt die Twig-Variante, wird unverändert das
`.html5` gerendert (voller Rückwärtskompatibilitäts-Fallback).

## Funktionsweise

`MetaModels\Render\Template::parse()` fragt vor dem Einbinden des Legacy-`.html5` den
`MetaModels\Render\TwigTemplateSurrogate` ab. Dieser prüft im gemanagten Contao-Twig-Loader
(`contao.twig.filesystem_loader`), ob ein passendes Twig-Template existiert, und rendert es via
`twig`. Der Twig-Context wird über Contaos `ContextFactory::fromData()` aus den Template-Daten
gebaut – dieselben Variablen wie im `.html5` stehen zur Verfügung.

Das entspricht 1:1 Contaos eigenem Surrogat-Mechanismus
(`\Contao\Template::renderTwigSurrogateIfExists()`).

### Namensschema

Aus dem Legacy-Namen wird der Twig-Identifier gebildet:

```
@Contao/metamodels/<gruppe>/<leaf>.html.twig
```

* **Gruppe** kommt aus dem Render-Kontext: `attribute`, `filter` oder `item`.
* **Leaf** ist der Template-Name ohne das konventionelle Legacy-Präfix.

| Legacy `.html5`        | Gruppe      | Twig-Identifier                     |
|------------------------|-------------|-------------------------------------|
| `mm_attr_text`         | `attribute` | `metamodels/attribute/text`         |
| `mm_filter_default`    | `filter`    | `metamodels/filter/default`         |
| `mm_filteritem_...`    | `filter`    | `metamodels/filter/...`             |
| `mm_default`           | `item`      | `metamodels/item/default`           |

Für eigene Templates gilt dieselbe Regel: `mm_attr_text_fancy` wird zu
`metamodels/attribute/text_fancy` — das Präfix entfällt, der Rest wird zum Leaf.

### Textformat

Neben der sichtbaren Ausgabe (`html5`) kann auch das Format `text` (Suchindex, Sortierung,
Gruppen-Header) auf Twig laufen. Die Templates dafür tragen ein zusätzliches `.text` im
Namen:

| Legacy            | Twig-Datei                | Twig-Identifier                  |
|-------------------|---------------------------|----------------------------------|
| `mm_attr_text.html5` | `attribute/text.html.twig`      | `metamodels/attribute/text`      |
| `mm_attr_text.text`  | `attribute/text.text.html.twig` | `metamodels/attribute/text.text` |

Die doppelte Endung ist **kein Schönheitsfehler, sondern zwingend**. Contao bildet den
Identifier, indem es das abschließende `.html.twig` bzw. `.twig` abschneidet, und verbietet
gemischte Typen unter einem Identifier. Ein `text.text.twig` neben `text.html.twig` hätte
also denselben Identifier `…/text`, aber einen anderen Typ — der `ContaoFilesystemLoader`
bricht dann den Aufbau der **gesamten** Hierarchie mit einer `OutOfBoundsException` ab:

```
The "metamodels/item/prerendered" template has incompatible types,
got "html.twig/html5" in "…/prerendered.html.twig" and "text.twig" in "…/prerendered.text.twig".
```

Das legt Backend **und** Frontend komplett lahm, nicht nur das betroffene Template. Mit
`.text.html.twig` bleibt die echte Endung `html.twig`, und die Textvariante bekommt den
eigenen Identifier `…/text.text`. Dieselbe Benennung nutzt bereits
`email_metamodels_notelist.text.html.twig` im Paket `notelist`.

Ein projekteigenes `templates/mm_attr_text.text` behält weiterhin Vorrang vor dem
mitgelieferten Twig-Template — genauso wie im Format `html5`.

### Frontend und Backend

Der Vorrang gilt in **beiden** Scopes. Die Backend-Listen rendern Attribute ebenfalls mit
`html5` (siehe `ItemRendererListener`), daher wirken Attribut-Twig-Templates auch dort.
**Standardtemplates müssen deshalb backend-tauglich bleiben** (schlanke `div`/`span`-Wrapper,
wie die bisherigen `.html5`). Für abweichende Backend-Darstellung empfiehlt sich ein eigenes
Render-Setting mit eigener Template-Auswahl.

## Twig-Templates in einem Paket bereitstellen

Der Contao-Twig-Loader behandelt den Legacy-Ordner `Resources/contao/templates` **flach**
(Unterordner werden verworfen, der Identifier wäre nur der Dateiname). Damit die
`metamodels/<gruppe>/<leaf>`-Struktur erhalten bleibt, müssen die Templates – genau wie in
Contaos eigenen Bundles – unter einem **Namespace-Root** liegen: ein Ordner `twig/` mit einer
leeren Marker-Datei **`.twig-root`**.

```
src/CoreBundle/Resources/contao/templates/
└── twig/
    ├── .twig-root                              (leere Marker-Datei, einmal pro Paket)
    └── metamodels/
        ├── attribute/
        │   └── text.html.twig
        ├── filter/
        │   └── default.html.twig
        └── item/
            └── default.html.twig
```

(In Paketen mit moderner Struktur entsprechend unter `contao/templates/twig/…`.)

Es ist **kein PHP-Code** nötig – die Dateien werden vom Loader automatisch unter `@Contao`
erfasst.

## Template Studio, Themes und Overrides

Weil die Templates im gemanagten `@Contao`-Namespace liegen (Untergruppe `metamodels/`), sind
sie ohne Zusatzaufwand:

* im **Template Studio** sichtbar und editierbar,
* über **Theme-Ordner** und das globale Projekt-`templates/`-Verzeichnis überschreibbar.

Ein höher priorisiertes `.html5` in der gemanagten Hierarchie (z. B. ein Projekt-Override am
neuen Pfad `templates/metamodels/<gruppe>/<leaf>.html5`) behält gegenüber einem Paket-Twig-Template
den Vorrang – ebenfalls wie in Contao.

### Legacy-Flach-Override (Übergangslösung, remove in 3.0)

Zusätzlich behält ein Override am **flachen** Legacy-Namen im Projekt-`templates/`-Ordner (oder in
einem Theme-Ordner) – z. B. `templates/metamodel_prerendered.html5` – weiterhin Vorrang vor einem
Paket-Twig-Template. `Template::hasLegacyTemplateOverride()` erkennt solche Overrides (Pfad unterhalb
`%kernel.project_dir%/templates`, per DI injiziert) und überspringt dann den Twig-Surrogaten. So
funktionieren bestehende Anpassungen nach dem Upgrade unverändert weiter.

**Diese Rücksichtnahme ist bewusst als `@deprecated` markiert und entfällt in 3.0** gemeinsam mit den
`.html5`-Templates – Overrides sollten nach `templates/metamodels/<gruppe>/…` umgezogen werden.

## Rollout-Stand

* **MetaModels Core:** Mechanismus implementiert (`Template`, `TemplateFactory`,
  `TwigTemplateSurrogate`, DI) inkl. Legacy-Flach-Override-Vorrang (Übergangslösung). Als erste
  Core-Twig-Templates ausgeliefert: `item/prerendered`, `item/unrendered`, `item/prerendered_debug`
  sowie `filter/default`, `filter/checkbox`, `filter/radiobuttons`, `filter/linklist`,
  `filter/datepicker`. `FrontendFilter` rendert die Filter-Widgets über die MetaModels-Engine.
* **Attribute-Templates:** werden schrittweise paketweise als Twig-Varianten nachgezogen.
