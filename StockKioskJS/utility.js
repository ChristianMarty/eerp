function preserveUrlParameters(document)
{
    const as = document.querySelectorAll('a');
    as.forEach(aTag => {
        aTag.href = aTag.href + location.search;
    })
}
