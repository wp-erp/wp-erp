import { useState, useRef, useEffect } from "react";

const OrganizationStep = ({ onNext, initialData = {} }) => {
    const [selectedCard, setSelectedCard] = useState(null);
    const [selectedItems, setSelectedItems] = useState({
        departments: initialData.departments || [],
        designations: initialData.designations || []
    });
    const [inputValue, setInputValue] = useState("");
    const [showSuggestions, setShowSuggestions] = useState(false);
    const inputRef = useRef(null);

    const departmentSuggestions = [
        "HR and Admin",
        "Engineering",
        "Sales",
        "Content Marketing",
        "Design",
        "Digital Marketing"
    ];

    const designationSuggestions = [
        "Manager",
        "Senior Developer",
        "Developer",
        "Designer",
        "Team Lead",
        "Consultant"
    ];

    const currentSuggestions =
        selectedCard === "departments"
            ? departmentSuggestions
            : designationSuggestions;

    // Show all suggestions filtered by input (including selected ones)
    const filteredSuggestions = currentSuggestions.filter(
        item => item.toLowerCase().includes(inputValue.toLowerCase())
    );
    
    const isItemSelected = (item) => {
        return selectedItems[selectedCard]?.includes(item) || false;
    };

    const handleCardClick = cardType => {
        if (selectedCard === cardType) {
            setSelectedCard(null);
            setInputValue("");
            setShowSuggestions(false);
        } else {
            setSelectedCard(cardType);
            setInputValue("");
            setShowSuggestions(false);
            setTimeout(() => {
                inputRef.current?.focus();
            }, 100);
        }
    };

    const addTag = value => {
        if (!value.trim() || !selectedCard) return;

        const currentItems = selectedItems[selectedCard] || [];
        if (!currentItems.includes(value)) {
            setSelectedItems({
                ...selectedItems,
                [selectedCard]: [...currentItems, value]
            });
        }
        setInputValue("");
    };
    
    const toggleTag = value => {
        if (!value.trim() || !selectedCard) return;
        
        const currentItems = selectedItems[selectedCard] || [];
        if (currentItems.includes(value)) {
            // Remove if already selected
            setSelectedItems({
                ...selectedItems,
                [selectedCard]: currentItems.filter(item => item !== value)
            });
        } else {
            // Add if not selected
            setSelectedItems({
                ...selectedItems,
                [selectedCard]: [...currentItems, value]
            });
        }
    };

    const removeTag = (type, value) => {
        setSelectedItems({
            ...selectedItems,
            [type]: selectedItems[type].filter(item => item !== value)
        });
    };

    const handleInputKeyDown = e => {
        if (e.key === "Enter") {
            e.preventDefault();
            if (inputValue.trim()) {
                addTag(inputValue.trim());
            }
        }
    };

    const handleSubmit = e => {
        e.preventDefault();
        onNext(selectedItems);
    };

    return (
        <div>
            {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
            <div className="max-w-640px mx-auto overflow-visible">
                {/* Heading - matches h1 from setup.css */}
                <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
                    Make Your Department and Designation
                </h1>
                {/* Subtitle - matches .subtitle from setup.css */}
                <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
                    Enter you company name and start date.
                </p>

                <form onSubmit={handleSubmit} className="mb-0">
                    {/* Card Selection */}
                    <div className="flex gap-5 mb-16 px-85px justify-center">
                        {/* Departments Card */}
                        <div
                            className={`selection-card ${
                                selectedCard === "departments" ? "selected" : ""
                            }`}
                            onClick={() => handleCardClick("departments")}
                        >
                            <div className="mb-6">
                                <svg
                                    width="35"
                                    height="31"
                                    viewBox="0 0 35 31"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M27.5006 26.3978C27.9131 26.4319 28.3304 26.4492 28.7517 26.4492C30.4989 26.4492 32.1764 26.1505 33.7358 25.6013C33.7463 25.4687 33.7517 25.3346 33.7517 25.1992C33.7517 22.4378 31.5131 20.1992 28.7517 20.1992C27.7057 20.1992 26.7346 20.5204 25.9319 21.0696M27.5006 26.3978C27.5007 26.4149 27.5007 26.4321 27.5007 26.4492C27.5007 26.8242 27.4801 27.1944 27.4399 27.5586C24.5119 29.2386 21.1185 30.1992 17.5007 30.1992C13.8829 30.1992 10.4895 29.2386 7.56158 27.5586C7.52137 27.1944 7.50073 26.8242 7.50073 26.4492C7.50073 26.4321 7.50078 26.415 7.50086 26.398M27.5006 26.3978C27.4908 24.4369 26.9165 22.6094 25.9319 21.0696M25.9319 21.0696C24.1554 18.2912 21.0432 16.4492 17.5007 16.4492C13.9587 16.4492 10.8468 18.2907 9.07022 21.0686M9.07022 21.0686C8.26778 20.52 7.29733 20.1992 6.25195 20.1992C3.49053 20.1992 1.25195 22.4378 1.25195 25.1992C1.25195 25.3346 1.25733 25.4687 1.26789 25.6013C2.82728 26.1505 4.50473 26.4492 6.25195 26.4492C6.67252 26.4492 7.08905 26.4319 7.50086 26.398M9.07022 21.0686C8.08524 22.6087 7.5107 24.4365 7.50086 26.398M22.5007 6.44922C22.5007 9.21064 20.2622 11.4492 17.5007 11.4492C14.7393 11.4492 12.5007 9.21064 12.5007 6.44922C12.5007 3.68779 14.7393 1.44922 17.5007 1.44922C20.2622 1.44922 22.5007 3.68779 22.5007 6.44922ZM32.5007 11.4492C32.5007 13.5203 30.8218 15.1992 28.7507 15.1992C26.6797 15.1992 25.0007 13.5203 25.0007 11.4492C25.0007 9.37815 26.6797 7.69922 28.7507 7.69922C30.8218 7.69922 32.5007 9.37815 32.5007 11.4492ZM10.0007 11.4492C10.0007 13.5203 8.3218 15.1992 6.25073 15.1992C4.17966 15.1992 2.50073 13.5203 2.50073 11.4492C2.50073 9.37815 4.17966 7.69922 6.25073 7.69922C8.3218 7.69922 10.0007 9.37815 10.0007 11.4492Z"
                                        stroke="#0F172A"
                                        strokeWidth="1.5"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-base font-semibold text-gray-900 m-0">
                                Departments
                            </h3>
                        </div>

                        {/* Designations Card */}
                        <div
                            className={`selection-card ${
                                selectedCard === "designations"
                                    ? "selected"
                                    : ""
                            }`}
                            onClick={() => handleCardClick("designations")}
                        >
                            <div className="mb-6">
                                <svg
                                    width="33"
                                    height="32"
                                    viewBox="0 0 33 32"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M30.25 19.7824V26.8658C30.25 28.6899 28.9385 30.2591 27.1303 30.4991C23.6524 30.9609 20.104 31.1992 16.5 31.1992C12.896 31.1992 9.34756 30.9609 5.86974 30.4991C4.06149 30.2591 2.75 28.6899 2.75 26.8658V19.7824M30.25 19.7824C31.0365 19.1119 31.5 18.0973 31.5 17.014V10.7087C31.5 8.90713 30.2202 7.35058 28.4384 7.08398C26.5628 6.80334 24.6658 6.5878 22.75 6.43976M30.25 19.7824C29.9273 20.0575 29.5503 20.2746 29.1285 20.4149C25.1589 21.7346 20.9129 22.4492 16.5 22.4492C12.0871 22.4492 7.84116 21.7346 3.87148 20.4148C3.44974 20.2746 3.07268 20.0575 2.75 19.7824M2.75 19.7824C1.96346 19.1119 1.5 18.0973 1.5 17.014V10.7087C1.5 8.90714 2.77984 7.35058 4.56157 7.08399C6.43722 6.80334 8.33415 6.58781 10.25 6.43976M22.75 6.43976V4.94922C22.75 2.87815 21.0711 1.19922 19 1.19922H14C11.9289 1.19922 10.25 2.87815 10.25 4.94922V6.43976M22.75 6.43976C20.6876 6.28039 18.6033 6.19922 16.5 6.19922C14.3967 6.19922 12.3124 6.28039 10.25 6.43976M16.5 17.4492H16.5125V17.4617H16.5V17.4492Z"
                                        stroke="#0F172A"
                                        strokeWidth="1.5"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-base font-semibold text-gray-900 m-0">
                                Designations
                            </h3>
                        </div>
                    </div>

                    {/* Multiselect Section (shown when card is selected) */}
                    {selectedCard && (
                        <div className="mb-8">
                            <div className="multiselect-container">
                                {/* Input with dropdown */}
                                <div className="relative">
                                    <input
                                        ref={inputRef}
                                        type="text"
                                        value={inputValue}
                                        onChange={e => {
                                            setInputValue(e.target.value);
                                            setShowSuggestions(true);
                                        }}
                                        onKeyDown={handleInputKeyDown}
                                        onFocus={() => setShowSuggestions(true)}
                                        onBlur={() => {
                                            setTimeout(
                                                () => setShowSuggestions(false),
                                                200
                                            );
                                        }}
                                        className="input"
                                        placeholder={`Type and press Enter to add ${selectedCard}...`}
                                    />

                                    {/* Suggestions List */}
                                    {showSuggestions &&
                                        filteredSuggestions.length > 0 && (
                                            <div className="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md mt-1 shadow-lg z-50 max-h-48 overflow-y-auto">
                                                {filteredSuggestions.map(
                                                    (suggestion, index) => {
                                                        const selected = isItemSelected(suggestion);
                                                        return (
                                                            <div
                                                                key={index}
                                                                className={`px-4 py-2.5 cursor-pointer transition-colors flex items-center justify-between ${
                                                                    selected 
                                                                        ? 'bg-blue-50 hover:bg-blue-100' 
                                                                        : 'hover:bg-gray-50'
                                                                }`}
                                                                onClick={() =>
                                                                    toggleTag(suggestion)
                                                                }
                                                            >
                                                                <span className={selected ? 'text-gray-900 font-medium' : 'text-gray-700'}>
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
                                                    }
                                                )}
                                            </div>
                                        )}
                                </div>

                                {/* Tags List */}
                                {selectedItems[selectedCard]?.length > 0 && (
                                    <div className="flex flex-wrap gap-2 mt-3">
                                        {selectedItems[selectedCard].map(
                                            (item, index) => (
                                                <div
                                                    key={index}
                                                    className="tag"
                                                >
                                                    <span className="tag-text">
                                                        {item}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        className="tag-remove"
                                                        onClick={() =>
                                                            removeTag(
                                                                selectedCard,
                                                                item
                                                            )
                                                        }
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
                                            )
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Button Container - matches erp-button-container with exact margin */}
                    <div className="mt-138.8px text-center">
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
