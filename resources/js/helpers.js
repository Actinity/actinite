export function trimObject(obj) {
    return Object.entries(obj)
        .filter(([_, v]) => v)
        .reduce((acc, [k, v]) => ({ ...acc, [k]: v }), {});
}

export function snapshotNode(node, fieldData) {
    let clonedData = JSON.parse(JSON.stringify(fieldData));
    let nodeData = {
        name: node.name,
        slug: node.slug,
        is_ready: node.is_ready,
        ordering: node.ordering,
        parent_id: node.parent_id,
        published_at: node.published_at,
        page_template: node.page_template
    }

    /*for(let key in clonedData) {
        if(clonedData.hasOwnProperty(key)) {
            //console.log(clonedData[key]);
            clonedData[key] = (""+clonedData[key]).replace(/\s+/,'-');
        }
    }*/

    return JSON.stringify([nodeData,trimObject(clonedData)]);
}
