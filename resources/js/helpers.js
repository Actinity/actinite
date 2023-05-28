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

    // When snapshotting, don't care about type differences
    // for basics like nulls/undefineds or ints vs string ints.

    Object.keys(clonedData).forEach(key => {
        if(!clonedData[key]) {
            clonedData[key] = null;
        }

        if(typeof clonedData[key] !== 'number') {
            let parsed = parseInt(clonedData[key]);
            if(clonedData[key] === ''+parsed) {
                clonedData[key] = parsed;
            }
        }
    });

    return JSON.stringify([nodeData,trimObject(clonedData)]);
}
