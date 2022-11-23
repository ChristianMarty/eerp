<template>
  <div class="documentation">
    <p>  In this document, the following verbal forms are used:
      “shall” indicates a requirement
      “should” indicates a recommendation
      “may” indicates a permission
      “can” indicates a possibility or a capability</p>

    <h1>Numbering Code</h1>
    <p>&ltprefix&gt-&ltnumber&gt-&ltindex&gt#&ltparameter1&gt#&ltparameter2&gt</p>

    <p><b>Prefix</b>  (alphabetical, may be optional, context-dependent)</p>
    <p>The prefix is used to determine what category a code belongs to.
      The prefix can be optional if, in context, it is clear what category a code belongs to.</p>

      <p><b>Number</b>  (numerical, mandatory)</p>
    <p>The Number part of the code shall uniquely identify an item within a given category.</p>

    <p><b>Index</b>  (numerical, should be optional)</p>
    <p>The index allows for further identification of subitems belonging to the main item.
      Index 0 and an unspecified index shall behave identically.</p>

    <p><b>Parameter</b>  (alphanumerical, shall be optional)</p>
    <p>Parameters allow passing additional information. The function of a parameter can be freely defined for each item.
      Multiple parameters can be permitted.</p>

  </div>
</template>
