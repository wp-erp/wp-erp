import { useState, useRef } from "react";

const TAG_ROW_LIMIT = 2;
const TAG_PER_ROW = 4;
const VISIBLE_LIMIT = TAG_ROW_LIMIT * TAG_PER_ROW;

const TagList = ({ items, onRemove }) => {
    const [expanded, setExpanded] = useState(false);

    if (!items.length) return null;

    const visible = expanded ? items : items.slice(0, VISIBLE_LIMIT);
    const hiddenCount = items.length - VISIBLE_LIMIT;

    return (
        <div className="flex flex-wrap gap-2 mt-4">
            {visible.map((item, index) => (
                <div key={index} className="tag">
                    <span className="tag-text">{item}</span>
                    <button
                        type="button"
                        className="tag-remove"
                        onClick={() => onRemove(item)}
                    >
                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 14 14"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5"
                                stroke="currentColor"
                                strokeWidth="1.5"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            ))}
            {!expanded && hiddenCount > 0 && (
                <button
                    type="button"
                    onClick={() => setExpanded(true)}
                    className="tag"
                    style={{
                        cursor: "pointer",
                        background: "#EFF6FF",
                        color: "#3B82F6",
                        border: "1px solid #BFDBFE"
                    }}
                >
                    +{hiddenCount} more
                </button>
            )}
            {expanded && hiddenCount > 0 && (
                <button
                    type="button"
                    onClick={() => setExpanded(false)}
                    className="tag"
                    style={{
                        cursor: "pointer",
                        background: "#EFF6FF",
                        color: "#3B82F6",
                        border: "1px solid #BFDBFE"
                    }}
                >
                    Show less
                </button>
            )}
        </div>
    );
};

const SelectField = ({
    label,
    placeholder,
    type,
    suggestions,
    selectedItems,
    onToggle,
    onAdd,
    onRemove
}) => {
    const [inputValue, setInputValue] = useState("");
    const [showSuggestions, setShowSuggestions] = useState(false);
    const inputRef = useRef(null);

    const filtered = suggestions
        .filter(item => item.toLowerCase().includes(inputValue.toLowerCase()))
        .sort((a, b) => {
            const aSelected = selectedItems.includes(a);
            const bSelected = selectedItems.includes(b);
            if (aSelected && !bSelected) return -1;
            if (!aSelected && bSelected) return 1;
            return 0;
        });

    const isCustomEntry =
        inputValue.trim() &&
        !suggestions.some(
            item => item.toLowerCase() === inputValue.toLowerCase()
        );

    const isSelected = item => selectedItems.includes(item);

    const handleKeyDown = e => {
        if (e.key === "Enter") {
            e.preventDefault();
            if (inputValue.trim()) {
                onAdd(type, inputValue.trim());
                setInputValue("");
            }
        }
    };

    return (
        <div className="mb-16">
            <label className="block text-sm font-medium text-gray-700 mb-2">
                {label}
            </label>
            <div className="multiselect-container">
                <div className="relative">
                    <input
                        ref={inputRef}
                        type="text"
                        value={inputValue}
                        onChange={e => {
                            setInputValue(e.target.value);
                            setShowSuggestions(true);
                        }}
                        onKeyDown={handleKeyDown}
                        onFocus={() => setShowSuggestions(true)}
                        onBlur={() =>
                            setTimeout(() => setShowSuggestions(false), 200)
                        }
                        className="input pr-10"
                        placeholder={placeholder}
                    />
                    <span className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center">
                        <svg
                            className={`transition-transform duration-200 ${
                                showSuggestions ? "rotate-180" : ""
                            }`}
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M4 6L8 10L12 6"
                                stroke="#9CA3AF"
                                strokeWidth="1.5"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </span>

                    {showSuggestions && (filtered.length > 0 || isCustomEntry) && (
                        <div className="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md mt-1 shadow-lg z-50 max-h-48 overflow-y-auto dropdown-scroll">
                            {isCustomEntry && (
                                <div
                                    className="px-4 py-2.5 cursor-pointer transition-colors flex items-center gap-2 hover:bg-gray-50 border-b border-gray-200"
                                    onClick={() => {
                                        onAdd(type, inputValue.trim());
                                        setInputValue("");
                                        setShowSuggestions(false);
                                    }}
                                >
                                    <svg
                                        width="16"
                                        height="16"
                                        viewBox="0 0 16 16"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="flex-shrink-0"
                                    >
                                        <path
                                            d="M8 3.33334V12.6667M3.33333 8H12.6667"
                                            stroke="#3B82F6"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                    <span className="text-gray-900 font-medium">
                                        Add "{inputValue.trim()}"
                                    </span>
                                </div>
                            )}
                            {filtered.map((suggestion, index) => {
                                const selected = isSelected(suggestion);
                                return (
                                    <div
                                        key={index}
                                        className="px-4 py-2.5 cursor-pointer transition-colors flex items-center justify-between hover:bg-gray-50"
                                        onClick={() =>
                                            onToggle(type, suggestion)
                                        }
                                    >
                                        <span className="text-gray-700 text-sm">
                                            {suggestion}
                                        </span>
                                        {selected && (
                                            <svg
                                                width="16"
                                                height="16"
                                                viewBox="0 0 16 16"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="flex-shrink-0 ml-2"
                                            >
                                                <path
                                                    d="M13.3332 4L5.99984 11.3333L2.6665 8"
                                                    stroke="#3B82F6"
                                                    strokeWidth="2"
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                />
                                            </svg>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                <TagList
                    items={selectedItems}
                    onRemove={item => onRemove(type, item)}
                />
            </div>
        </div>
    );
};

const OrganizationStep = ({ onNext, initialData = {} }) => {
    const [selectedItems, setSelectedItems] = useState({
        departments: initialData.departments || [],
        designations: initialData.designations || []
    });

    const departmentSuggestions = [
        "General Management",
        "Operations Department",
        "Finance Department",
        "Sales Department",
        "Human Resource Department",
        "Purchase Department",
        "Engineering Department",
        "Production Department",
        "Procurement Department"
    ];

    const designationSuggestions = [
        "President",
        "Vice President",
        "CEO",
        "Managing Director",
        "Product Manager",
        "Project Manager",
        "Program Manager",
        "Operations Manager",
        "Marketing Manager",
        "Business Manager",
        "Technology Manager",
        "Finance/Accounts Manager",
        "Human Resource Manager",
        "Hiring Manager",
        "Senior Engineer",
        "Engineer",
        "Junior Engineer",
        "Business Executive",
        "Marketing Executive",
        "Customer Support Executive"
    ];

    const handleToggle = (type, value) => {
        const current = selectedItems[type] || [];
        setSelectedItems({
            ...selectedItems,
            [type]: current.includes(value)
                ? current.filter(i => i !== value)
                : [...current, value]
        });
    };

    const handleAdd = (type, value) => {
        const current = selectedItems[type] || [];
        if (!current.includes(value)) {
            setSelectedItems({ ...selectedItems, [type]: [...current, value] });
        }
    };

    const handleRemove = (type, value) => {
        setSelectedItems({
            ...selectedItems,
            [type]: selectedItems[type].filter(i => i !== value)
        });
    };

    const handleSubmit = e => {
        e.preventDefault();
        onNext(selectedItems);
    };

    return (
        <div>
            <div className="max-w-640px mx-auto overflow-visible">
                <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
                    Make Your Department and Designation
                </h1>
                <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
                    Set up your departments and job designations
                </p>

                <form onSubmit={handleSubmit} className="mb-0">
                    <SelectField
                        label="Select Department"
                        placeholder="Add or Select Department"
                        type="departments"
                        suggestions={departmentSuggestions}
                        selectedItems={selectedItems.departments}
                        onToggle={handleToggle}
                        onAdd={handleAdd}
                        onRemove={handleRemove}
                    />

                    <SelectField
                        label="Select Designation"
                        placeholder="Add or Select Designation"
                        type="designations"
                        suggestions={designationSuggestions}
                        selectedItems={selectedItems.designations}
                        onToggle={handleToggle}
                        onAdd={handleAdd}
                        onRemove={handleRemove}
                    />

                    <div className="mt-btn text-center">
                        <button
                            type="submit"
                            className="btn-primary no-underline"
                        >
                            Next
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default OrganizationStep;
