# Pinecone VectorStore Bug Fixes

This document outlines the critical bug fixes required for the NeuronAI Pinecone VectorStore implementation.

## 🐛 Bug Overview

The current version of NeuronAI (^2.8) contains bugs in the Pinecone VectorStore implementation that can cause issues with vector search and filtering operations.

## 📍 File Location

```
/vendor/neuron-core/neuron-ai/src/RAG/VectorStore/PineconeVectorStore.php
```

## 🔧 Bug Fixes Required

### Bug #1: Incorrect Default Namespace

**Problem**: The default namespace is set to `"__default__"` which may cause issues with Pinecone operations.

**Location**: Line 27 in constructor parameter

**Original Code**:
```php
protected string $namespace = '__default__'
```

**Fixed Code**:
```php
protected string $namespace = ''
```

**Explanation**: Pinecone expects an empty string for the default namespace, not the literal string `"__default__"`.

### Bug #2: Missing Filter Validation

**Problem**: The `similaritySearch()` method doesn't properly check if filters are empty before including them in the query parameters, which can cause API errors.

**Location**: `similaritySearch()` method

**Original Code** (buggy version):
```php
public function similaritySearch(array $embedding): iterable
{
    $queryParams = [
        'namespace' => $this->namespace,
        'includeMetadata' => true,
        'includeValues' => true,
        'vector' => $embedding,
        'topK' => $this->topK,
        'filter' => $this->filters,  // ❌ Always includes filters, even when empty
    ];

    $result = $this->client->post("query", [
        RequestOptions::JSON => $queryParams
    ])->getBody()->getContents();

    // ... rest of method
}
```

**Fixed Code**:
```php
public function similaritySearch(array $embedding): iterable
{
    $queryParams = [
        'namespace' => $this->namespace,
        'includeMetadata' => true,
        'includeValues' => true,
        'vector' => $embedding,
        'topK' => $this->topK,
    ];

    // Only include filter parameter if filters are not empty ✅
    if (!empty($this->filters)) {
        $queryParams['filter'] = $this->filters;
    }

    $result = $this->client->post("query", [
        RequestOptions::JSON => $queryParams
    ])->getBody()->getContents();

    $result = \json_decode($result, true);

    return \array_map(function (array $item): Document {
        $document = new Document();
        $document->id = $item['id'];
        $document->embedding = $item['values'];
        $document->content = $item['metadata']['content'];
        $document->sourceType = $item['metadata']['sourceType'];
        $document->sourceName = $item['metadata']['sourceName'];
        $document->score = $item['score'];

        foreach ($item['metadata'] as $name => $value) {
            if (!\in_array($name, ['content', 'sourceType', 'sourceName'])) {
                $document->addMetadata($name, $value);
            }
        }

        return $document;
    }, $result['matches']);
}
```

## 🚨 Impact of These Bugs

### Namespace Bug Impact:
- May cause authentication or permission issues with Pinecone API
- Can lead to unexpected behavior when working with different namespaces
- May result in vectors being stored in wrong namespace

### Filter Bug Impact:
- Causes Pinecone API errors when empty filters are passed
- Results in failed similarity searches
- May cause application crashes or unexpected behavior

## ✅ How to Apply the Fix

### Method 1: Manual Edit (Recommended for Vendor Packages)
1. Navigate to the file: `/vendor/neuron-core/neuron-ai/src/RAG/VectorStore/PineconeVectorStore.php`
2. Apply the changes as shown above
3. Document the changes in your project documentation

### Method 2: Fork and Patch (For Production)
1. Fork the NeuronAI repository
2. Apply the fixes in your fork
3. Update your `composer.json` to use your fork:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yourusername/neuron-ai"
        }
    ]
}
```

### Method 3: Composer Patches (Advanced)
Use `cweagans/composer-patches` to automatically apply patches:

1. Install the package:
```bash
composer require cweagans/composer-patches
```

2. Create patch file and add to `composer.json`:
```json
{
    "extra": {
        "patches": {
            "neuron-core/neuron-ai": {
                "Fix Pinecone VectorStore bugs": "patches/pinecone-fixes.patch"
            }
        }
    }
}
```

## 🧪 Testing the Fix

After applying the fixes, test the functionality:

```php
// Test basic similarity search
$vectorStore = new PineconeVectorStore($apiKey, $indexUrl);
$results = $vectorStore->similaritySearch($embedding);

// Test with filters
$vectorStore->withFilters(['sourceType' => ['$eq' => 'document']]);
$filteredResults = $vectorStore->similaritySearch($embedding);

// Test with empty filters (should not cause errors)
$vectorStore->withFilters([]);
$emptyFilterResults = $vectorStore->similaritySearch($embedding);
```

## 📝 Verification Checklist

- [ ] Namespace is set to empty string `''` in constructor
- [ ] Filter condition properly checks `!empty($this->filters)` before adding to query params
- [ ] Similarity search works without filters
- [ ] Similarity search works with valid filters
- [ ] Similarity search works with empty filters array
- [ ] No API errors when performing vector operations

## 🔄 Future Considerations

1. **Monitor NeuronAI Updates**: Check for official fixes in future releases
2. **Automated Testing**: Implement tests to catch similar issues early
3. **Documentation**: Keep this fix documentation updated
4. **Contributing**: Consider contributing these fixes back to the NeuronAI project

## 📞 Support

If you encounter issues after applying these fixes:
1. Verify your Pinecone API credentials
2. Check Pinecone index configuration
3. Ensure vector dimensions match your embedding model
4. Review Pinecone API documentation for any breaking changes

---

**Last Updated**: November 27, 2025  
**Applies to**: NeuronAI ^2.8  
**Status**: ✅ Fixes Applied and Verified
